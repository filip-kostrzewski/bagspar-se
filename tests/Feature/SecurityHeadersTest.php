<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_refuses_framing_and_content_sniffing(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_pages_whose_url_is_a_secret_send_no_referrer(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('a', 48),
        ])->save();

        $this->get(route('client-status.show', ['token' => $project->client_status_token]))
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer');

        $this->get(route('auth.password.reset', ['token' => 'anything']))
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }
}
