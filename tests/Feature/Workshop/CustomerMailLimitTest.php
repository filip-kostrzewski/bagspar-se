<?php

namespace Tests\Feature\Workshop;

use App\Mail\ClientStatusLinkMail;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Support\CustomerMailLimit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerMailLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_workshop_cannot_mail_customers_beyond_the_daily_cap(): void
    {
        Mail::fake();
        config(['bagspar.customer_mail_daily_limit' => 2]);
        $owner = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'email' => 'ada@example.test',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
        ]);
        $project->issueClientStatusToken();
        $project->client_status_enabled = true;
        $project->save();

        $this->actingAs($owner)->post(route('app.projects.client-status.send', $project))->assertSessionHasNoErrors();
        $this->actingAs($owner)->post(route('app.projects.client-status.send', $project))->assertSessionHasNoErrors();
        $this->actingAs($owner)
            ->post(route('app.projects.client-status.send', $project))
            ->assertSessionHasErrors(['client_status' => CustomerMailLimit::MESSAGE]);

        Mail::assertSent(ClientStatusLinkMail::class, 2);
    }

    public function test_the_cap_is_counted_per_workshop(): void
    {
        config(['bagspar.customer_mail_daily_limit' => 1]);
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertTrue(CustomerMailLimit::allow($first->workshop));
        $this->assertFalse(CustomerMailLimit::allow($first->workshop));
        $this->assertTrue(CustomerMailLimit::allow($second->workshop));
    }
}
