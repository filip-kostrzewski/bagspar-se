<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClientStatusIntakeApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function publicProject(string $token = 'a'): Project
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat($token, 48),
        ])->save();

        return $project;
    }

    public function test_the_section_shows_only_when_there_are_notes_or_before_photos(): void
    {
        $project = $this->publicProject();
        $status = route('client-status.show', ['token' => $project->client_status_token]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'intake_notes' => null,
            'notes' => 'ARBETSHEMLIGHET',
        ]);
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'moment' => 'after']);

        $this->get($status)
            ->assertOk()
            ->assertDontSee('Mottagningsprotokoll', false)
            ->assertDontSee('Godkänn mottagningsprotokollet', false);

        $sash->update(['intake_notes' => 'Spricka i nedre rutan.']);

        $this->get($status)
            ->assertOk()
            ->assertSee('Mottagningsprotokoll', false)
            ->assertSee('Spricka i nedre rutan.', false)
            ->assertSee('Godkänn mottagningsprotokollet', false)
            ->assertDontSee('ARBETSHEMLIGHET', false);

        $sash->update(['intake_notes' => null]);
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'moment' => 'before']);

        $this->get($status)
            ->assertOk()
            ->assertSee('Mottagningsprotokoll', false)
            ->assertSee('Ingen anmärkning', false);
    }

    public function test_the_customer_approves_once_and_the_name_is_escaped(): void
    {
        $this->travelTo(Carbon::parse('2026-10-16 10:15:00', 'UTC'));
        $project = $this->publicProject();
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'intake_notes' => 'Röta.']);
        $status = route('client-status.show', ['token' => $project->client_status_token]);
        $approve = route('client-status.intake-approve', ['token' => $project->client_status_token]);

        $this->from($status)
            ->post($approve, [])
            ->assertRedirect($status)
            ->assertSessionHasErrors(['name' => 'Ange ditt namn.'], null, 'intake');
        $this->from($status)
            ->post($approve, ['name' => str_repeat('Å', 151)])
            ->assertSessionHasErrors(['name' => 'Namnet får vara högst 150 tecken.'], null, 'intake');
        $this->assertNull($project->fresh()->intake_approved_at);

        $this->from($status)
            ->followingRedirects()
            ->post($approve, ['name' => str_repeat('Å', 151)])
            ->assertSee('Namnet får vara högst 150 tecken.', false)
            ->assertSee('aria-invalid="true"', false);

        $this->from($status)
            ->post($approve, ['name' => '  <script>alert(1)</script>  '])
            ->assertRedirect($status);

        $project->refresh();
        $this->assertSame('<script>alert(1)</script>', $project->intake_approved_name);
        $this->assertSame('link', $project->intake_approved_via);
        $this->assertTrue($project->intake_approved_at?->equalTo(Carbon::parse('2026-10-16 10:15:00', 'UTC')));
        $this->assertDatabaseHas('project_events', [
            'project_id' => $project->getKey(),
            'user_id' => null,
            'user_name' => '<script>alert(1)</script>',
            'kind' => ProjectEvent::KIND_INTAKE_APPROVED,
        ]);

        $this->get($status)
            ->assertOk()
            ->assertSee('Mottagningsprotokollet godkändes 16 okt 2026 12:15 av &lt;script&gt;alert(1)&lt;/script&gt;.', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('intake-approval-name', false);

        $this->from($status)
            ->post($approve, ['name' => 'Någon annan'])
            ->assertRedirect($status)
            ->assertSessionHas('status', 'Mottagningsprotokollet är redan godkänt.');

        $this->assertSame('<script>alert(1)</script>', $project->fresh()->intake_approved_name);
        $this->assertSame(1, ProjectEvent::query()->where('kind', ProjectEvent::KIND_INTAKE_APPROVED)->count());
    }

    public function test_nothing_is_stored_when_the_protocol_is_empty(): void
    {
        $project = $this->publicProject('e');
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'intake_notes' => null]);

        $this->post(route('client-status.intake-approve', ['token' => $project->client_status_token]), ['name' => 'Kiko'])
            ->assertRedirect();

        $this->assertNull($project->fresh()->intake_approved_at);
    }

    public function test_the_approval_is_refused_when_the_status_link_is_off_or_unknown(): void
    {
        $project = $this->publicProject('b');
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'intake_notes' => 'Röta.']);
        $project->forceFill(['client_status_enabled' => false])->save();

        $this->post(route('client-status.intake-approve', ['token' => $project->client_status_token]), ['name' => 'Kiko'])
            ->assertNotFound();
        $this->assertNull($project->fresh()->intake_approved_at);

        $this->post('/status/'.str_repeat('c', 48).'/mottagning/godkann', ['name' => 'Kiko'])->assertNotFound();
        $this->post('/status/kort/mottagning/godkann', ['name' => 'Kiko'])->assertNotFound();
    }
}
