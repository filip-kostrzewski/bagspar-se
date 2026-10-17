<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use App\Support\SashQrCodeRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkshopWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workshop_owner_can_create_projects_and_update_sash_details(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('app.projects.store'), [
                'name' => 'Villa Ek',
                'customer_name' => 'Familjen Ek',
                'location' => 'Uppsala',
                'notes' => 'Varsam lagning av södra fasaden.',
            ])
            ->assertRedirect();

        $project = Project::query()->where('name', 'Villa Ek')->firstOrFail();
        $this->assertSame($owner->workshop_id, $project->workshop_id);

        $this->post(route('app.sashes.store', $project), [
            'code' => 'S-12',
            'room' => 'Kök',
            'location' => 'Vänster fönster',
            'notes' => 'Behåll originalglaset.',
        ])->assertRedirect();

        $sash = $project->windowSashes()->firstOrFail();
        $this->assertSame('received', $sash->stage);

        $this->patch(route('app.projects.update', $project), [
            'name' => 'Villa Ek, norra flygeln',
            'customer_name' => 'Familjen Ek',
            'location' => 'Uppsala',
            'notes' => 'Arbetet godkänt.',
        ])->assertRedirect();

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'code' => 'S-12-N',
            'room' => 'Kök',
            'location' => 'Vänster fönster',
            'stage' => 'repair',
            'notes' => 'Nederstycket lagas.',
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'id' => $project->getKey(),
            'name' => 'Villa Ek, norra flygeln',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('window_sashes', [
            'id' => $sash->getKey(),
            'code' => 'S-12-N',
            'stage' => 'repair',
            'notes' => 'Nederstycket lagas.',
        ]);

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'stage' => 'finish',
        ])->assertSessionHasErrors('stage');

        $this->patch(route('app.projects.update', $project), [
            'status' => 'deleted',
        ])->assertSessionHasErrors('status');
    }

    public function test_project_and_sash_array_inputs_receive_swedish_validation_errors(): void
    {
        $this->app->setLocale('en');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->patch(route('app.projects.update', $project), [
                'name' => ['Ej giltigt'],
            ])->assertSessionHasErrors(['name' => 'Ange projektnamnet som text.']);

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'notes' => ['Ej giltigt'],
        ])->assertSessionHasErrors(['notes' => 'Ange anteckningarna som text.']);
    }

    public function test_dashboard_only_lists_projects_for_the_signed_in_workshop(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $ownProject = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Eget projekt',
        ]);
        Project::factory()->create([
            'workshop_id' => $otherOwner->workshop_id,
            'name' => 'Annat företags projekt',
        ]);

        $this->actingAs($owner)->get(route('app.dashboard'))
            ->assertOk()
            ->assertSee('Eget projekt')
            ->assertDontSee('Annat företags projekt');

        $this->get(route('app.projects.show', $ownProject))
            ->assertOk()
            ->assertSee($ownProject->name);
    }

    public function test_workcard_qr_is_a_private_svg_for_the_scoped_absolute_workcard_url(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $otherProject = Project::factory()->create(['workshop_id' => $otherOwner->workshop_id]);
        $otherSash = WindowSash::factory()->create(['project_id' => $otherProject->getKey()]);

        $response = $this->actingAs($owner)->get(route('app.sashes.qr', [$project, $sash]));
        $expectedSvg = (new SashQrCodeRenderer)->render(route('app.sashes.show', [$project, $sash]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertSee('<svg', false);

        $this->assertSame($expectedSvg, $response->getContent());

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringNotContainsString('public', $cacheControl);

        $this->get(route('app.sashes.qr', [$otherProject, $otherSash]))->assertNotFound();
    }

    public function test_every_project_sash_report_and_photo_endpoint_is_tenant_scoped(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $ownProject = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $ownSash = WindowSash::factory()->create(['project_id' => $ownProject->getKey()]);
        $otherProject = Project::factory()->create(['workshop_id' => $otherOwner->workshop_id]);
        $otherSash = WindowSash::factory()->create(['project_id' => $otherProject->getKey()]);
        $otherPhoto = $this->createStoredPhoto($otherSash);

        $this->actingAs($owner);

        $this->get(route('app.projects.show', $otherProject))->assertNotFound();
        $this->get(route('app.projects.report', $otherProject))->assertNotFound();
        $this->get(route('app.projects.labels', $otherProject))->assertNotFound();
        $this->patch(route('app.projects.update', $otherProject), ['status' => 'completed'])->assertNotFound();
        $this->post(route('app.sashes.store', $otherProject), ['code' => 'FORBIDDEN'])->assertNotFound();
        $this->get(route('app.sashes.show', [$otherProject, $otherSash]))->assertNotFound();
        $this->get(route('app.sashes.label', [$otherProject, $otherSash]))->assertNotFound();
        $this->patch(route('app.sashes.update', [$otherProject, $otherSash]), ['stage' => 'ready'])->assertNotFound();
        $this->post(route('app.sashes.photos.store', [$otherProject, $otherSash]), [
            'photo' => UploadedFile::fake()->image('private.jpg', 80, 60),
        ])->assertNotFound();
        $this->get(route('app.sashes.photos.show', [$otherProject, $otherSash, $otherPhoto]))->assertNotFound();
        $this->get(route('app.sashes.show', [$ownProject, $otherSash]))->assertNotFound();
        $this->get(route('app.sashes.label', [$ownProject, $otherSash]))->assertNotFound();
        $this->get(route('app.sashes.photos.show', [$ownProject, $ownSash, $otherPhoto]))->assertNotFound();

        $this->assertDatabaseCount('sash_photos', 1);
    }

    public function test_uploaded_photos_are_reencoded_and_served_from_private_storage(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->actingAs($owner)
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->image('before.jpg', 120, 90),
                'moment' => 'before',
            ])
            ->assertRedirect();

        $photo = $sash->photos()->firstOrFail();
        $this->assertSame('before', $photo->moment);
        $this->assertSame('before.jpg', $photo->original_filename);
        $this->assertSame('image/webp', $photo->mime_type);
        $this->assertStringEndsWith('.webp', $photo->path);
        Storage::disk('local')->assertExists($photo->path);

        $response = $this->get(route('app.sashes.photos.show', [$project, $sash, $photo]));
        $response->assertOk()
            ->assertHeader('Content-Type', 'image/webp')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringNotContainsString('public', $cacheControl);
        $this->assertSame('RIFF', substr(Storage::disk('local')->get($photo->path), 0, 4));

        $this->get(route('app.projects.report', $project))->assertOk()->assertSee(route('app.sashes.photos.show', [$project, $sash, $photo]));
    }

    public function test_project_photo_and_report_dates_use_stockholm_calendar_day_while_storage_stays_utc(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 22:30:00', 'UTC'));
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $photo = SashPhoto::factory()->create(['sash_id' => $sash->getKey()]);

        $this->assertSame('2026-10-15 22:30:00', $project->fresh()->getRawOriginal('created_at'));
        $this->assertSame('2026-10-15 22:30:00', $photo->fresh()->getRawOriginal('created_at'));

        $this->actingAs($owner)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('<p class="photo-card__caption">Före · 16 okt 2026</p>', false);

        $this->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertSee('<dd>16 okt 2026 00:30</dd>', false)
            ->assertSee('class="report-footer__product">Bågspår</span>', false)
            ->assertSee('Före · 16 okt 2026', false)
            ->assertDontSee('window.webp', false)
            ->assertDontSee('Rapport från Bågspår', false);
    }

    public function test_jpeg_exif_orientations_are_applied_and_metadata_is_stripped(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $this->actingAs($owner);

        $expectedCorners = [
            2 => [[20, 220, 20], [240, 20, 20], [240, 220, 20], [20, 20, 240]],
            3 => [[240, 220, 20], [20, 20, 240], [20, 220, 20], [240, 20, 20]],
            4 => [[20, 20, 240], [240, 220, 20], [240, 20, 20], [20, 220, 20]],
            5 => [[240, 20, 20], [20, 20, 240], [20, 220, 20], [240, 220, 20]],
            6 => [[20, 20, 240], [240, 20, 20], [240, 220, 20], [20, 220, 20]],
            7 => [[240, 220, 20], [20, 220, 20], [20, 20, 240], [240, 20, 20]],
            8 => [[20, 220, 20], [240, 220, 20], [240, 20, 20], [20, 20, 240]],
        ];

        foreach ($expectedCorners as $orientation => $corners) {
            $source = $this->jpegWithExifOrientation($orientation);
            $this->assertStringContainsString("Exif\0\0", $source);

            $this->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->createWithContent('oriented-'.$orientation.'.jpg', $source),
                'moment' => 'after',
            ])->assertRedirect();

            $photo = $sash->photos()->latest('id')->firstOrFail();
            $webp = Storage::disk('local')->get($photo->path);
            $this->assertStringNotContainsString('Exif', $webp);

            $image = imagecreatefromstring($webp);
            $this->assertInstanceOf(\GdImage::class, $image);
            $expectedDimensions = in_array($orientation, [5, 6, 7, 8], true) ? [60, 80] : [80, 60];
            $this->assertSame($expectedDimensions, [imagesx($image), imagesy($image)]);

            [$topLeft, $topRight, $bottomLeft, $bottomRight] = $corners;
            $this->assertPixelColorNear($image, 5, 5, $topLeft);
            $this->assertPixelColorNear($image, imagesx($image) - 6, 5, $topRight);
            $this->assertPixelColorNear($image, 5, imagesy($image) - 6, $bottomLeft);
            $this->assertPixelColorNear($image, imagesx($image) - 6, imagesy($image) - 6, $bottomRight);
            imagedestroy($image);
        }
    }

    public function test_photo_upload_rejects_unsupported_files_oversized_images_and_excess_photos(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $this->actingAs($owner);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->create('unsafe.svg', 1, 'image/svg+xml'),
                'moment' => 'before',
            ])->assertSessionHasErrors('photo');

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->image('too-wide.jpg', 6001, 1),
                'moment' => 'before',
            ])->assertSessionHasErrors('photo');

        for ($index = 0; $index < 20; $index++) {
            $path = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/'.$index.'.webp';
            Storage::disk('local')->put($path, 'private webp test bytes');
            $sash->photos()->create([
                'path' => $path,
                'original_filename' => null,
                'mime_type' => 'image/webp',
                'size' => 23,
            ]);
        }

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->image('too-many.jpg', 80, 60),
                'moment' => 'before',
            ])->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('sash_photos', 20);
    }

    public function test_photo_upload_requires_a_before_or_after_moment(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $this->actingAs($owner);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->image('missing-moment.jpg', 80, 60),
            ])->assertSessionHasErrors(['moment' => 'Välj om bilden är tagen före eller efter.']);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'photo' => UploadedFile::fake()->image('unknown-moment.jpg', 80, 60),
                'moment' => 'during',
            ])->assertSessionHasErrors(['moment' => 'Välj före eller efter.']);

        $this->assertDatabaseCount('sash_photos', 0);
    }

    public function test_photo_upload_defaults_to_before_until_a_before_photo_exists_and_the_moment_can_change(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $this->actingAs($owner);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('name="moment" type="radio" value="before" required checked', false)
            ->assertDontSee('name="moment" type="radio" value="after" checked', false);

        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'photo' => UploadedFile::fake()->image('fore.jpg', 80, 60),
            'moment' => 'before',
        ])->assertRedirect()->assertSessionHas('status', 'Sparat');

        $photo = SashPhoto::query()->firstOrFail();
        $this->assertSame('before', $photo->moment);
        $this->assertNotNull($photo->path);

        $this->patch(route('app.sashes.photos.update', [$project, $sash, $photo]), [
            'moment' => 'after',
        ])->assertRedirect()->assertSessionHas('status', 'Bildens tillfälle har sparats.');

        $photo->refresh();
        $this->assertSame('after', $photo->moment);
        $this->assertTrue(Storage::disk('local')->exists($photo->path));

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('class="photo-card__caption">Efter · ', false)
            ->assertSee('name="moment" type="radio" value="before" required checked', false)
            ->assertDontSee('name="moment" type="radio" value="after" checked', false);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.photos.store', [$project, $sash]), [
                'form_context' => 'photo',
                'moment' => 'during',
                'photo' => UploadedFile::fake()->image('bad.jpg', 80, 60),
            ])->assertSessionHasErrors('moment');

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertSee('name="moment" type="radio" value="before" required checked', false)
            ->assertDontSee('name="moment" type="radio" value="after" checked', false);

        SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'moment' => 'before',
        ]);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertSee('name="moment" type="radio" value="after" checked', false)
            ->assertDontSee('name="moment" type="radio" value="before" required checked', false);

        $nullSash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        SashPhoto::factory()->create([
            'sash_id' => $nullSash->getKey(),
            'moment' => null,
        ]);

        $this->get(route('app.sashes.show', [$project, $nullSash]))
            ->assertSee('name="moment" type="radio" value="after" checked', false)
            ->assertDontSee('name="moment" type="radio" value="before" required checked', false);

        $this->actingAs($other)
            ->patch(route('app.sashes.photos.update', [$project, $sash, $photo]), ['moment' => 'before'])
            ->assertNotFound();
        $this->assertSame('after', $photo->fresh()?->moment);
    }

    public function test_sash_photos_without_a_moment_show_fore_and_are_ordered_before_then_after(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $after = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'moment' => 'after',
            'original_filename' => 'efter.jpg',
        ]);
        $before = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'moment' => 'before',
            'original_filename' => 'fore.jpg',
        ]);
        $untagged = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'moment' => null,
            'original_filename' => 'utan.jpg',
        ]);
        $after->forceFill(['created_at' => '2026-10-01 08:00:00', 'updated_at' => '2026-10-01 08:00:00'])->save();
        $untagged->forceFill(['created_at' => '2026-10-02 08:00:00', 'updated_at' => '2026-10-02 08:00:00'])->save();
        $before->forceFill(['created_at' => '2026-10-16 08:00:00', 'updated_at' => '2026-10-16 08:00:00'])->save();

        $this->actingAs($owner)->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertDontSee('Välj före eller efter', false)
            ->assertSee('id="moment-'.$untagged->getKey().'-before" value="before" name="moment" checked', false)
            ->assertSee('id="moment-'.$after->getKey().'-after" value="after" name="moment" checked', false)
            ->assertSeeInOrder([
                route('app.sashes.photos.show', [$project, $sash, $untagged]),
                route('app.sashes.photos.show', [$project, $sash, $before]),
                route('app.sashes.photos.show', [$project, $sash, $after]),
            ], false);
    }

    public function test_guest_cannot_open_private_workshop_routes_or_photo_urls(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $photo = $this->createStoredPhoto($sash);

        $this->get(route('app.dashboard'))->assertRedirect(route('login'));
        $this->get(route('app.projects.report', $project))->assertRedirect(route('login'));
        $this->get(route('app.projects.labels', $project))->assertRedirect(route('login'));
        $this->get(route('app.sashes.label', [$project, $sash]))->assertRedirect(route('login'));
        $this->get(route('app.settings.edit'))->assertRedirect(route('login'));
        $this->get(route('app.sashes.qr', [$project, $sash]))->assertRedirect(route('login'));
        $this->get(route('app.sashes.photos.show', [$project, $sash, $photo]))->assertRedirect(route('login'));
    }

    public function test_project_sash_cards_show_the_latest_after_photo_or_else_a_before_photo(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $withAfter = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'A1',
        ]);
        $withBefore = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'B1',
        ]);
        $empty = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'C1',
        ]);
        $olderBefore = SashPhoto::factory()->create([
            'sash_id' => $withAfter->getKey(),
            'moment' => 'before',
        ]);
        $olderAfter = SashPhoto::factory()->create([
            'sash_id' => $withAfter->getKey(),
            'moment' => 'after',
        ]);
        $latestAfter = SashPhoto::factory()->create([
            'sash_id' => $withAfter->getKey(),
            'moment' => 'after',
        ]);
        $olderNull = SashPhoto::factory()->create([
            'sash_id' => $withBefore->getKey(),
            'moment' => null,
        ]);
        $latestBefore = SashPhoto::factory()->create([
            'sash_id' => $withBefore->getKey(),
            'moment' => 'before',
        ]);
        $olderBefore->forceFill(['created_at' => '2026-10-01 08:00:00', 'updated_at' => '2026-10-01 08:00:00'])->save();
        $olderAfter->forceFill(['created_at' => '2026-10-02 08:00:00', 'updated_at' => '2026-10-02 08:00:00'])->save();
        $latestAfter->forceFill(['created_at' => '2026-10-16 08:00:00', 'updated_at' => '2026-10-16 08:00:00'])->save();
        $olderNull->forceFill(['created_at' => '2026-10-01 08:00:00', 'updated_at' => '2026-10-01 08:00:00'])->save();
        $latestBefore->forceFill(['created_at' => '2026-10-10 08:00:00', 'updated_at' => '2026-10-10 08:00:00'])->save();

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('class="sash-card__thumb" src="'.route('app.sashes.photos.show', [$project, $withAfter, $latestAfter]).'" alt="" width="64" height="64" loading="lazy" decoding="async"', false)
            ->assertSee('class="sash-card__thumb" src="'.route('app.sashes.photos.show', [$project, $withBefore, $latestBefore]).'" alt="" width="64" height="64" loading="lazy" decoding="async"', false)
            ->assertDontSee(route('app.sashes.photos.show', [$project, $withAfter, $olderAfter]), false)
            ->assertDontSee(route('app.sashes.photos.show', [$project, $withAfter, $olderBefore]), false)
            ->assertDontSee(route('app.sashes.photos.show', [$project, $withBefore, $olderNull]), false)
            ->assertSee('sash-card__thumb--empty', false)
            ->assertSeeInOrder([$withAfter->code, $withBefore->code, $empty->code], false);
    }

    private function createStoredPhoto(WindowSash $sash): SashPhoto
    {
        $path = 'sash-photos/'.$sash->project_id.'/'.$sash->getKey().'/'.fake()->uuid().'.webp';
        Storage::disk('local')->put($path, 'private webp test bytes');

        return $sash->photos()->create([
            'path' => $path,
            'original_filename' => 'private.webp',
            'mime_type' => 'image/webp',
            'size' => 23,
        ]);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $expected
     */
    private function assertPixelColorNear(\GdImage $image, int $x, int $y, array $expected): void
    {
        $actual = imagecolorsforindex($image, imagecolorat($image, $x, $y));

        $this->assertEqualsWithDelta($expected[0], $actual['red'], 70);
        $this->assertEqualsWithDelta($expected[1], $actual['green'], 70);
        $this->assertEqualsWithDelta($expected[2], $actual['blue'], 70);
    }

    private function jpegWithExifOrientation(int $orientation): string
    {
        $image = imagecreatetruecolor(80, 60);
        $red = imagecolorallocate($image, 240, 20, 20);
        $green = imagecolorallocate($image, 20, 220, 20);
        $blue = imagecolorallocate($image, 20, 20, 240);
        $yellow = imagecolorallocate($image, 240, 220, 20);

        imagefilledrectangle($image, 0, 0, 39, 29, $red);
        imagefilledrectangle($image, 40, 0, 79, 29, $green);
        imagefilledrectangle($image, 0, 30, 39, 59, $blue);
        imagefilledrectangle($image, 40, 30, 79, 59, $yellow);

        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        if (! is_string($jpeg) || ! str_starts_with($jpeg, "\xFF\xD8")) {
            self::fail('Could not create the JPEG orientation fixture.');
        }

        $tiff = "II\x2A\x00\x08\x00\x00\x00"
            ."\x01\x00"
            ."\x12\x01\x03\x00\x01\x00\x00\x00"
            .chr($orientation)."\x00\x00\x00"
            ."\x00\x00\x00\x00";
        $exif = "Exif\x00\x00".$tiff;
        $app1 = "\xFF\xE1".pack('n', strlen($exif) + 2).$exif;

        return substr($jpeg, 0, 2).$app1.substr($jpeg, 2);
    }
}
