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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SashAndPhotoDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_delete_a_photo_and_the_history_keeps_their_name(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $member = User::factory()->member()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Bo Berg',
        ]);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'K1']);
        $path = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/fore.webp';
        Storage::disk('local')->put($path, 'fore-bytes');
        $photo = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'path' => $path,
            'moment' => 'before',
            'original_filename' => 'fonster-fore.webp',
        ]);

        $this->actingAs($member)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Ta bort bild', false)
            ->assertSee('data-confirm-title="Ta bort bilden?"', false)
            ->assertSee('data-confirm="Filen tas bort från lagringen."', false);

        $this->delete(route('app.sashes.photos.destroy', [$project, $sash, $photo]))
            ->assertRedirect(route('app.sashes.show', [$project, $sash]))
            ->assertSessionHas('status', 'Bilden har tagits bort.');

        $this->assertModelMissing($photo);
        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'user_id' => $member->getKey(),
            'user_name' => 'Bo Berg',
            'kind' => SashEvent::KIND_PHOTO,
            'body' => 'Bilden togs bort: fonster-fore.webp (Före).',
        ]);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Bilden togs bort: fonster-fore.webp (Före).', false)
            ->assertSee('Bo Berg', false);
    }

    public function test_a_member_cannot_delete_a_sash_and_does_not_see_the_action(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'V2']);
        $path = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/kvar.webp';
        Storage::disk('local')->put($path, 'kvar');
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $path]);

        $this->actingAs($member)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertDontSee('Ta bort båge', false);

        $this->delete(route('app.sashes.destroy', [$project, $sash]))->assertForbidden();

        $this->assertModelExists($sash);
        Storage::disk('local')->assertExists($path);
        $this->assertSame(0, ProjectEvent::query()->count());
    }

    public function test_the_owner_can_delete_a_sash_and_keeps_a_project_history_entry(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['name' => 'Anna Andersson']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Villa Ek']);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'B-24']);
        $kept = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'B-25']);
        $photoPath = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/bild.webp';
        $unsafePath = 'workshop-logos/logo.webp';
        $escapedPath = 'sash-photos/../secrets.webp';
        Storage::disk('local')->put($photoPath, 'bild');
        Storage::disk('local')->put($unsafePath, 'logo');
        Storage::disk('local')->put('secrets.webp', 'secret');
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $photoPath, 'moment' => 'after']);
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $unsafePath]);
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $escapedPath]);
        $keptPath = 'sash-photos/'.$project->getKey().'/'.$kept->getKey().'/kvar.webp';
        Storage::disk('local')->put($keptPath, 'kvar');
        SashPhoto::factory()->create(['sash_id' => $kept->getKey(), 'path' => $keptPath]);

        $this->actingAs($owner)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Ta bort båge', false)
            ->assertSee('data-confirm="Ta bort bågen B-24? Bildfilerna tas bort."', false);

        $this->delete(route('app.sashes.destroy', [$project, $sash]))
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHas('status', 'Bågen och bildfilerna har tagits bort.');

        $this->assertModelMissing($sash);
        $this->assertModelExists($kept);
        $this->assertModelExists($project);
        Storage::disk('local')->assertMissing($photoPath);
        Storage::disk('local')->assertExists($unsafePath);
        Storage::disk('local')->assertExists('secrets.webp');
        Storage::disk('local')->assertExists($keptPath);
        $this->assertDatabaseHas('project_events', [
            'project_id' => $project->getKey(),
            'user_id' => $owner->getKey(),
            'user_name' => 'Anna Andersson',
            'kind' => ProjectEvent::KIND_SASH_REMOVED,
            'body' => 'Bågen B-24 togs bort.',
        ]);
        $this->assertSame(0, SashEvent::query()->where('sash_id', $sash->getKey())->count());

        $this->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Bågen B-24 togs bort.', false)
            ->assertSee('Anna Andersson', false)
            ->assertDontSee('>B-24<', false);
    }

    public function test_photo_and_sash_deletion_is_limited_to_the_workshop(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $otherSash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $path = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/bild.webp';
        Storage::disk('local')->put($path, 'bild');
        $photo = SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $path]);

        $this->delete(route('app.sashes.photos.destroy', [$project, $sash, $photo]))
            ->assertRedirect(route('login'));
        $this->delete(route('app.sashes.destroy', [$project, $sash]))
            ->assertRedirect(route('login'));

        $this->actingAs($other)
            ->delete(route('app.sashes.photos.destroy', [$project, $sash, $photo]))
            ->assertNotFound();
        $this->actingAs($other)
            ->delete(route('app.sashes.destroy', [$project, $sash]))
            ->assertNotFound();
        $this->actingAs($owner)
            ->delete(route('app.sashes.photos.destroy', [$project, $otherSash, $photo]))
            ->assertNotFound();

        $this->assertModelExists($photo);
        $this->assertModelExists($sash);
        Storage::disk('local')->assertExists($path);
    }

    public function test_uploading_a_photo_records_who_added_it(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['name' => 'Ada Ek']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photos' => [UploadedFile::fake()->image('fonster-fore.jpg', 80, 60)],
                'moment' => 'before',
            ])
            ->assertRedirect(route('app.sashes.show', [$project, $sash]));

        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'user_id' => $owner->getKey(),
            'user_name' => 'Ada Ek',
            'kind' => SashEvent::KIND_PHOTO,
            'body' => 'Bilden lades till: fonster-fore.jpg (Före).',
        ]);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Bilden lades till: fonster-fore.jpg (Före).', false);
    }
}
