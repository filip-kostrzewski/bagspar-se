<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\SashEvent;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IntakeProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_intake_notes_records_a_sash_event_and_shows_on_the_sash_page(): void
    {
        $owner = User::factory()->create(['name' => 'Anna Andersson']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'stage' => 'received',
            'notes' => null,
            'intake_notes' => null,
        ]);

        $this->actingAs($owner)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Skick vid mottagning', false)
            ->assertSee('Sprickor, röta, saknade beslag, lösa rutor. Visas i mottagningsprotokollet.', false);

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'form_context' => 'sash',
            'stage' => 'received',
            'notes' => '',
            'intake_notes' => 'Spricka i nedre rutan.',
        ])->assertRedirect()->assertSessionHas('status', 'Sparat');

        $this->assertSame('Spricka i nedre rutan.', $sash->fresh()->intake_notes);
        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'user_name' => 'Anna Andersson',
            'kind' => SashEvent::KIND_NOTE,
            'body' => 'Skick vid mottagning: Spricka i nedre rutan.',
        ]);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Spricka i nedre rutan.', false)
            ->assertSee('Skick vid mottagning: Spricka i nedre rutan.', false);

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'form_context' => 'sash',
            'intake_notes' => 'Spricka i nedre rutan.',
        ])->assertRedirect()->assertSessionHas('status', 'Inga ändringar');
        $this->assertSame(1, SashEvent::query()->where('sash_id', $sash->getKey())->count());

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'intake_notes' => str_repeat('a', 2001),
        ])->assertSessionHasErrors(['intake_notes' => 'Skicket vid mottagning får vara högst 2 000 tecken.']);
    }

    public function test_the_project_page_counts_notes_and_photos_and_shows_the_approval_state(): void
    {
        $this->travelTo(Carbon::parse('2026-10-16 10:15:00', 'UTC'));
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $noted = WindowSash::factory()->create(['project_id' => $project->getKey(), 'intake_notes' => 'Röta nere till vänster.']);
        $photographed = WindowSash::factory()->create(['project_id' => $project->getKey(), 'intake_notes' => null]);
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'intake_notes' => null]);
        SashPhoto::factory()->create(['sash_id' => $photographed->getKey(), 'moment' => 'before']);
        SashPhoto::factory()->create(['sash_id' => $noted->getKey(), 'moment' => 'after']);

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSeeInOrder(['Mottagningsprotokoll', 'Statuslänk'])
            ->assertSee('1 av 3 bågar har anmärkningar vid mottagning. 1 har bilder före.', false)
            ->assertSee('Inte godkänt.', false)
            ->assertSee(route('app.projects.intake', $project), false)
            ->assertSee(route('app.projects.intake.pdf', $project), false)
            ->assertSee('Kunden godkände på plats', false)
            ->assertDontSee('Ta bort godkännandet', false);

        $project->forceFill([
            'intake_approved_at' => now(),
            'intake_approved_name' => 'Kiko',
            'intake_approved_via' => 'link',
        ])->save();

        $this->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Godkänt 16 okt 2026 12:15 av Kiko (via statuslänken).', false)
            ->assertDontSee('Kunden godkände på plats', false)
            ->assertSee('Ta bort godkännandet', false);
    }

    public function test_the_intake_page_renders_notes_and_before_photos_only(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'notes' => 'PROJEKTHEMLIGHET',
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'K-1',
            'room' => 'Kök',
            'location' => 'Vänster',
            'notes' => 'ARBETSHEMLIGHET',
            'intake_notes' => '<b>Spricka</b> i glaset.',
        ]);
        $empty = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'K-2',
            'intake_notes' => null,
        ]);
        $before = SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'moment' => 'before']);
        $after = SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'moment' => 'after']);

        $this->actingAs($owner)
            ->get(route('app.projects.intake', $project))
            ->assertOk()
            ->assertSee('Mottagningsprotokoll', false)
            ->assertSee('Protokollet beskriver bågarnas skick när de togs emot av verkstaden.', false)
            ->assertSee('K-1', false)
            ->assertSee('Kök · Vänster', false)
            ->assertSee('&lt;b&gt;Spricka&lt;/b&gt; i glaset.', false)
            ->assertDontSee('<b>Spricka</b>', false)
            ->assertSee('Ingen anmärkning', false)
            ->assertSee(route('app.sashes.photos.show', [$project, $sash, $before]), false)
            ->assertDontSee(route('app.sashes.photos.show', [$project, $sash, $after]), false)
            ->assertDontSee('ARBETSHEMLIGHET', false)
            ->assertDontSee('PROJEKTHEMLIGHET', false)
            ->assertSee('Inte godkänt av kunden.', false);

        $project->forceFill([
            'intake_approved_at' => Carbon::parse('2026-10-16 10:15:00', 'UTC'),
            'intake_approved_name' => 'Kiko',
            'intake_approved_via' => 'on-site',
        ])->save();

        $this->get(route('app.projects.intake', $project))
            ->assertOk()
            ->assertSee('Godkänt 16 okt 2026 12:15 av Kiko (på plats).', false)
            ->assertDontSee('Inte godkänt av kunden.', false);
        $this->assertModelExists($empty);
    }

    public function test_the_pdf_downloads_with_the_before_photos_embedded(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Villa Ek']);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'intake_notes' => 'Spricka.']);

        $this->actingAs($owner);
        $plain = $this->get(route('app.projects.intake.pdf', $project));
        $plain->assertOk();
        $plain->assertHeader('Content-Type', 'application/pdf');
        $plain->assertHeader('Content-Disposition', 'attachment; filename="mottagningsprotokoll-villa-ek.pdf"');
        $this->assertStringStartsWith('%PDF', (string) $plain->getContent());
        $this->assertStringNotContainsString("\xFF\xD8\xFF", (string) $plain->getContent());

        foreach (range(1, 5) as $number) {
            $this->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->image('fore'.$number.'.jpg', 40, 30),
                'moment' => 'before',
            ])->assertRedirect();
        }

        $withPhotos = $this->get(route('app.projects.intake.pdf', $project))->getContent();
        $this->assertIsString($withPhotos);
        $this->assertStringStartsWith('%PDF', $withPhotos);
        $this->assertStringContainsString("\xFF\xD8\xFF", $withPhotos);
        $this->assertStringNotContainsString('http://', $withPhotos);
        $this->assertStringNotContainsString('https://', $withPhotos);
    }

    public function test_an_on_site_approval_stores_the_name_and_records_the_event(): void
    {
        $this->travelTo(Carbon::parse('2026-10-16 10:15:00', 'UTC'));
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Bo Berg']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $route = route('app.projects.intake.approve', $project);

        $this->actingAs($member)
            ->from(route('app.projects.show', $project))
            ->post($route, ['name' => '   '])
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHasErrors(['name' => 'Ange kundens namn.']);
        $this->post($route, ['name' => str_repeat('Å', 151)])
            ->assertSessionHasErrors(['name' => 'Namnet får vara högst 150 tecken.']);
        $this->assertNull($project->fresh()->intake_approved_at);

        $this->from(route('app.projects.show', $project))
            ->post($route, ['name' => '  <i>Kiko</i>  '])
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHas('status', 'Mottagningsprotokollet är godkänt.');

        $project->refresh();
        $this->assertSame('<i>Kiko</i>', $project->intake_approved_name);
        $this->assertSame('on-site', $project->intake_approved_via);
        $this->assertTrue($project->intake_approved_at?->equalTo(Carbon::parse('2026-10-16 10:15:00', 'UTC')));
        $this->assertDatabaseHas('project_events', [
            'project_id' => $project->getKey(),
            'user_id' => $member->getKey(),
            'user_name' => 'Bo Berg',
            'kind' => ProjectEvent::KIND_INTAKE_APPROVED,
            'body' => 'Mottagningsprotokollet godkändes på plats av <i>Kiko</i>.',
        ]);

        $this->post($route, ['name' => 'Någon annan'])
            ->assertSessionHas('status', 'Mottagningsprotokollet är redan godkänt.');
        $this->assertSame('<i>Kiko</i>', $project->fresh()->intake_approved_name);
        $this->assertSame(1, ProjectEvent::query()->where('kind', ProjectEvent::KIND_INTAKE_APPROVED)->count());

        $this->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('&lt;i&gt;Kiko&lt;/i&gt;', false)
            ->assertDontSee('<i>Kiko</i>', false);
    }

    public function test_only_the_owner_can_remove_the_approval_and_others_get_404(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $project->forceFill([
            'intake_approved_at' => Carbon::parse('2026-10-16 10:15:00', 'UTC'),
            'intake_approved_name' => 'Kiko',
            'intake_approved_via' => 'link',
        ])->save();
        $destroy = route('app.projects.intake.approval.destroy', $project);

        $this->actingAs($member)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertDontSee('Ta bort godkännandet', false);
        $this->delete($destroy)->assertForbidden();
        $this->assertSame('Kiko', $project->fresh()->intake_approved_name);

        $this->actingAs($other)->delete($destroy)->assertNotFound();
        $this->get(route('app.projects.intake', $project))->assertNotFound();
        $this->get(route('app.projects.intake.pdf', $project))->assertNotFound();
        $this->post(route('app.projects.intake.approve', $project), ['name' => 'Intrång'])->assertNotFound();
        $this->assertSame('Kiko', $project->fresh()->intake_approved_name);

        $this->actingAs($owner)
            ->from(route('app.projects.show', $project))
            ->delete($destroy)
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHas('status', 'Godkännandet har tagits bort.');

        $fresh = $project->fresh();
        $this->assertNull($fresh->intake_approved_at);
        $this->assertNull($fresh->intake_approved_name);
        $this->assertNull($fresh->intake_approved_via);
    }

    public function test_guests_are_sent_to_the_login(): void
    {
        $project = Project::factory()->create();

        $this->get(route('app.projects.intake', $project))->assertRedirect(route('login'));
        $this->post(route('app.projects.intake.approve', $project), ['name' => 'Kiko'])->assertRedirect(route('login'));
    }
}
