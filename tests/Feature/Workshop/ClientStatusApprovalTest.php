<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClientStatusApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_approve_delivery_once_and_the_name_is_escaped(): void
    {
        $this->travelTo(Carbon::parse('2026-10-16 10:15:00', 'UTC'));

        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('a', 48),
        ])->save();
        $status = route('client-status.show', ['token' => $project->client_status_token]);

        $this->get($status)
            ->assertOk()
            ->assertSee('Godkänn leverans', false)
            ->assertSee('novalidate', false);

        $this->from($status)
            ->post(route('client-status.approve', ['token' => $project->client_status_token]), [])
            ->assertRedirect($status)
            ->assertSessionHasErrors([
                'name' => 'Ange ditt namn.',
                'confirm' => 'Bekräfta att du godkänner leveransen.',
            ]);
        $this->assertNull($project->fresh()->delivery_approved_at);

        $this->from($status)
            ->post(route('client-status.approve', ['token' => $project->client_status_token]), [
                'name' => str_repeat('Å', 121),
                'confirm' => '1',
            ])
            ->assertRedirect($status)
            ->assertSessionHasErrors([
                'name' => 'Namnet får vara högst 120 tecken.',
            ]);
        $this->assertNull($project->fresh()->delivery_approved_at);

        $this->from($status)
            ->post(route('client-status.approve', ['token' => $project->client_status_token]), [
                'name' => '  <script>alert(1)</script>  ',
                'confirm' => '1',
            ])
            ->assertRedirect($status)
            ->assertSessionHas('status', 'Leveransen är godkänd.');

        $project->refresh();
        $this->assertSame('<script>alert(1)</script>', $project->delivery_approved_name);
        $this->assertTrue($project->delivery_approved_at?->equalTo(Carbon::parse('2026-10-16 10:15:00', 'UTC')));

        $this->get($status)
            ->assertOk()
            ->assertSee('16 okt 2026 12:15', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('name="name"', false);

        $this->actingAs($owner)
            ->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertSee('Leverans godkänd', false)
            ->assertSee('16 okt 2026 12:15', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->post(route('auth.logout'));

        $this->from($status)
            ->post(route('client-status.approve', ['token' => $project->client_status_token]), [
                'name' => 'Någon annan',
                'confirm' => '1',
            ])
            ->assertRedirect($status)
            ->assertSessionHas('status', 'Leveransen är redan godkänd.');

        $this->assertSame('<script>alert(1)</script>', $project->fresh()->delivery_approved_name);
    }

    public function test_approval_is_refused_when_the_status_link_is_off_or_unknown(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
        ]);
        $project->forceFill([
            'client_status_enabled' => false,
            'client_status_token' => str_repeat('b', 48),
        ])->save();

        $this->post(route('client-status.approve', ['token' => $project->client_status_token]), [
            'name' => 'Kiko',
            'confirm' => '1',
        ])->assertNotFound();

        $this->assertNull($project->fresh()->delivery_approved_at);

        $this->post('/status/'.str_repeat('c', 48).'/godkann', [
            'name' => 'Kiko',
            'confirm' => '1',
        ])->assertNotFound();

        $this->post('/status/kort/godkann', [
            'name' => 'Kiko',
            'confirm' => '1',
        ])->assertNotFound();
    }
}
