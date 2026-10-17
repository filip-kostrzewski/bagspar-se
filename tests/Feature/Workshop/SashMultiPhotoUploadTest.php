<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashEvent;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SashMultiPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_upload_form_accepts_several_files_at_once(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('name="photos[]"', false)
            ->assertSee('multiple', false)
            ->assertDontSee('capture=', false)
            ->assertSee('Du kan välja flera bilder på en gång.');
    }

    public function test_three_files_are_stored_with_the_chosen_moment_and_one_event_each(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'moment' => 'after',
                'photos' => [
                    UploadedFile::fake()->image('a.jpg', 80, 60),
                    UploadedFile::fake()->image('b.png', 80, 60),
                    UploadedFile::fake()->image('c.jpg', 80, 60),
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('status', '3 bilder tillagda.');

        $photos = $sash->photos()->get();
        $this->assertCount(3, $photos);
        $this->assertSame(['after'], $photos->pluck('moment')->unique()->all());
        $this->assertEqualsCanonicalizing(['a.jpg', 'b.png', 'c.jpg'], $photos->pluck('original_filename')->all());

        foreach ($photos as $photo) {
            $this->assertSame('image/webp', $photo->mime_type);
            Storage::disk('local')->assertExists($photo->path);
        }

        $this->assertSame(3, SashEvent::query()
            ->where('sash_id', $sash->getKey())
            ->where('kind', SashEvent::KIND_PHOTO)
            ->count());
    }

    public function test_a_single_file_still_works_and_keeps_the_short_status(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'moment' => 'before',
                'photos' => [UploadedFile::fake()->image('a.jpg', 80, 60)],
            ])
            ->assertSessionHas('status', 'Sparat');

        $this->assertDatabaseCount('sash_photos', 1);
    }

    public function test_a_batch_that_would_exceed_twenty_photos_is_rejected_entirely(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        SashPhoto::factory()->count(18)->create(['sash_id' => $sash->getKey()]);

        $this->actingAs($owner)
            ->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'moment' => 'before',
                'photos' => [
                    UploadedFile::fake()->image('a.jpg', 80, 60),
                    UploadedFile::fake()->image('b.jpg', 80, 60),
                    UploadedFile::fake()->image('c.jpg', 80, 60),
                ],
            ])
            ->assertSessionHasErrors('photos');

        $this->assertDatabaseCount('sash_photos', 18);
        $this->assertSame([], Storage::disk('local')->allFiles('sash-photos/'.$project->getKey().'/'.$sash->getKey()));
        $this->assertSame(0, SashEvent::query()->where('kind', SashEvent::KIND_PHOTO)->count());

        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'moment' => 'before',
            'photos' => [UploadedFile::fake()->image('a.jpg', 80, 60), UploadedFile::fake()->image('b.jpg', 80, 60)],
        ])->assertSessionHas('status', '2 bilder tillagda.');

        $this->assertDatabaseCount('sash_photos', 20);
    }

    public function test_more_than_twenty_files_in_one_request_are_rejected(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $files = array_map(fn (int $number): UploadedFile => UploadedFile::fake()->image($number.'.jpg', 20, 20), range(1, 21));

        $this->actingAs($owner)
            ->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), ['moment' => 'before', 'photos' => $files])
            ->assertSessionHasErrors(['photos' => 'Du kan ladda upp högst 20 bilder åt gången.']);

        $this->assertDatabaseCount('sash_photos', 0);
    }

    public function test_one_bad_file_rejects_the_whole_batch(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'moment' => 'before',
                'photos' => [
                    UploadedFile::fake()->image('ok.jpg', 80, 60),
                    UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml'),
                    UploadedFile::fake()->image('ok2.jpg', 80, 60),
                ],
            ])
            ->assertSessionHasErrors(['photos.1' => 'Bild 2 måste vara en bild.']);

        $this->assertDatabaseCount('sash_photos', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('sash_events', 0);
    }

    public function test_an_oversized_or_too_large_image_in_the_batch_rejects_it(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $this->actingAs($owner)->from(route('app.sashes.show', [$project, $sash]));

        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'moment' => 'before',
            'photos' => [UploadedFile::fake()->image('ok.jpg', 80, 60), UploadedFile::fake()->image('big.jpg', 80, 60)->size(6000)],
        ])->assertSessionHasErrors(['photos.1' => 'Bild 2 får vara högst 5 MB.']);

        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'moment' => 'before',
            'photos' => [UploadedFile::fake()->image('ok.jpg', 80, 60), UploadedFile::fake()->image('wide.jpg', 4000, 3500)],
        ])->assertSessionHasErrors('photos');

        $this->assertDatabaseCount('sash_photos', 0);
    }

    public function test_the_upload_requires_at_least_one_file(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), ['moment' => 'before'])
            ->assertSessionHasErrors(['photos' => 'Välj minst en bild att ladda upp.']);
    }
}
