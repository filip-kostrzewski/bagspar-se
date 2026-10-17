<?php

namespace Tests\Feature\Workshop;

use App\Mail\ClientStatusLinkMail;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientStatusMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_workshop_can_mail_the_status_link_to_the_customer(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $owner->workshop->update(['name' => 'Ekens Fönster', 'contact_email' => 'verkstad@example.test']);
        $project = $this->project($owner, 'ada@example.test');
        $url = route('client-status.show', ['token' => $project->client_status_token]);

        $this->actingAs($member)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Skicka länken till kunden', false)
            ->assertSee('Mejlas till ada@example.test.', false);

        $this->actingAs($member)
            ->post(route('app.projects.client-status.send', $project))
            ->assertRedirect()
            ->assertSessionHas('status', 'Statuslänken har skickats till ada@example.test.');

        Mail::assertSent(ClientStatusLinkMail::class, function (ClientStatusLinkMail $mail) use ($url): bool {
            $rendered = $mail->render();

            return $mail->hasTo('ada@example.test')
                && $mail->hasReplyTo('verkstad@example.test')
                && $mail->url === $url
                && $mail->hasSubject('Följ arbetet med Villa Ek')
                && str_contains($rendered, $url)
                && str_contains($rendered, 'Ekens Fönster')
                && str_contains($rendered, 'Öppna statussidan');
        });
        $this->assertDatabaseHas('project_events', [
            'project_id' => $project->getKey(),
            'kind' => 'status-link-sent',
            'body' => 'Statuslänk skickad till ada@example.test.',
        ]);
    }

    public function test_the_link_cannot_be_mailed_before_it_is_switched_on(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $project = $this->project($owner, 'ada@example.test');
        $project->client_status_enabled = false;
        $project->save();

        $this->actingAs($owner)
            ->from(route('app.projects.show', $project))
            ->post(route('app.projects.client-status.send', $project))
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHasErrors(['client_status' => 'Slå på statuslänken först.']);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('project_events', 0);
    }

    public function test_the_link_cannot_be_mailed_when_the_customer_has_no_email(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $project = $this->project($owner, null);

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Kunden har ingen e-postadress än.', false);

        $this->post(route('app.projects.client-status.send', $project))
            ->assertSessionHasErrors(['client_status' => 'Kunden saknar e-postadress. Lägg till den under Kunder.']);

        Mail::assertNothingSent();
    }

    public function test_a_user_from_another_workshop_cannot_mail_the_link(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = $this->project($owner, 'ada@example.test');

        $this->actingAs($other)
            ->post(route('app.projects.client-status.send', $project))
            ->assertNotFound();

        Mail::assertNothingSent();
    }

    private function project(User $owner, ?string $email): Project
    {
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
            'email' => $email,
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
            'name' => 'Villa Ek',
        ]);
        $project->issueClientStatusToken();
        $project->client_status_enabled = true;
        $project->save();

        return $project;
    }
}
