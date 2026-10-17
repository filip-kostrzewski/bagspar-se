<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_owner_archives_by_default_and_deletion_removes_photo_files_after_confirmation(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $other = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'status' => 'active',
        ]);
        $project->issueClientStatusToken();
        $project->client_status_enabled = true;
        $project->save();
        $token = $project->client_status_token;
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $photo = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'path' => 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/bild.webp',
        ]);
        $outside = 'workshop-logos/'.$owner->workshop_id.'/behall.webp';
        Storage::disk('local')->put($photo->path, 'webp-bytes');
        Storage::disk('local')->put($outside, 'logo-bytes');
        $photo->update(['path' => $outside]);
        $kept = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'path' => 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/ratt.webp',
        ]);
        Storage::disk('local')->put($kept->path, 'sash-bytes');

        $this->actingAs($member)->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertDontSee('Arkivera projekt', false)
            ->assertDontSee('project-danger-zone', false);
        $this->post(route('app.projects.archive', $project), ['action' => 'archive'])->assertForbidden();
        $this->delete(route('app.projects.destroy', $project), ['confirmation' => 'ta-bort'])->assertForbidden();

        $this->actingAs($other)->post(route('app.projects.archive', $project), ['action' => 'archive'])->assertNotFound();
        $this->get(route('app.projects.confirm-destroy', $project))->assertNotFound();

        $script = file_get_contents(public_path('js/workshop.js'));
        $css = file_get_contents(public_path('css/workshop.css'));
        $this->assertIsString($script);
        $this->assertIsString($css);
        $this->assertStringNotContainsString('window.confirm', $script);
        $this->assertStringContainsString("event.key !== 'Tab'", $script);
        $this->assertStringContainsString('is-hiding', $script);
        $this->assertStringContainsString('.form-field > label.workshop-checkbox', $css);

        $this->actingAs($owner)->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('data-confirm="Arkivera projektet? Det döljs i den vanliga listan."', false)
            ->assertSee('aria-labelledby="workshop-confirm-title"', false)
            ->assertSee('aria-describedby="workshop-confirm-text"', false)
            ->assertSee('id="workshop-confirm-title" class="workshop-dialog__title">Bekräfta</h2>', false)
            ->assertSee('workshop-button--quiet" type="submit" value="cancel" data-confirm-cancel', false)
            ->assertSee('workshop-button--primary" type="submit" value="confirm" data-confirm-accept', false)
            ->assertSee('project-danger-zone', false)
            ->assertSee('Pågående projekt', false);

        $this->post(route('app.projects.archive', $project), ['action' => 'archive'])
            ->assertRedirect(route('app.dashboard', ['arkiv' => 1]))
            ->assertSessionHas('status', 'Projektet har arkiverats.');

        $this->assertNotNull($project->fresh()?->archived_at);
        $this->get(route('app.dashboard'))
            ->assertOk()
            ->assertSee('role="status" aria-live="polite"', false)
            ->assertSee('Projektet har arkiverats.', false)
            ->assertSeeInOrder(['Pågående projekt', '0'], false)
            ->assertDontSee('Villa Ek', false)
            ->assertDontSee('Ge första projektet en plats.', false)
            ->assertSee('Visa arkiverade (1)', false);
        $this->get(route('app.dashboard', ['arkiv' => 1]))
            ->assertOk()
            ->assertSee('Villa Ek', false)
            ->assertSee('Arkiverade projekt', false)
            ->assertSee('Arkiverad', false)
            ->assertDontSee('Pågår', false);
        $this->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Arkiverad', false)
            ->assertDontSee('Pågående projekt', false)
            ->assertSee('data-confirm="Återställ projektet till den vanliga listan?"', false);

        $this->post(route('app.projects.archive', $project), ['action' => 'restore'])
            ->assertRedirect(route('app.dashboard'))
            ->assertSessionHas('status', 'Projektet har återställts.');
        $this->assertNull($project->fresh()?->archived_at);

        $this->get(route('app.projects.confirm-destroy', $project))
            ->assertOk()
            ->assertSee('Ta bort Villa Ek?', false)
            ->assertSee('Arkivera i stället', false)
            ->assertSee('Det går inte att ångra.', false);

        $this->delete(route('app.projects.destroy', $project))->assertStatus(422);
        $this->assertModelExists($project);

        $this->delete(route('app.projects.destroy', $project), ['confirmation' => 'ta-bort'])
            ->assertRedirect(route('app.dashboard'))
            ->assertSessionHas('status', 'Projektet och bildfilerna har tagits bort.');

        $this->assertModelMissing($project);
        $this->assertDatabaseMissing('window_sashes', ['id' => $sash->getKey()]);
        $this->assertDatabaseMissing('sash_photos', ['id' => $kept->getKey()]);
        Storage::disk('local')->assertMissing($kept->path);
        Storage::disk('local')->assertExists($outside);
        $this->get(route('client-status.show', ['token' => $token]))->assertNotFound();
    }
}
