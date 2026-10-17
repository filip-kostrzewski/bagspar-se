<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use App\Support\EmbeddedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectReportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_report_downloads_as_a_private_pdf_with_embedded_photos(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'notes' => 'HEMLIGANTECKNING',
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'notes' => 'kittanteckning',
        ]);

        $this->actingAs($owner);
        $withoutNotes = $this->get(route('app.projects.report.pdf', $project));
        $withoutNotes->assertOk();
        $withoutNotes->assertHeader('Content-Type', 'application/pdf');
        $withoutNotes->assertHeader('Content-Disposition', 'attachment; filename="villa-ek-rapport.pdf"');
        $withoutNotes->assertHeader('X-Content-Type-Options', 'nosniff');
        $cache = (string) $withoutNotes->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cache);
        $this->assertStringContainsString('no-store', $cache);

        $plain = $withoutNotes->getContent();
        $this->assertIsString($plain);
        $this->assertStringStartsWith('%PDF', $plain);
        $this->assertStringNotContainsString('http://', $plain);
        $this->assertStringNotContainsString('https://', $plain);
        $this->assertStringNotContainsString("\xFF\xD8\xFF", $plain);

        $withNotes = $this->get(route('app.projects.report.pdf', [
            'project' => $project,
            'anteckningar' => 1,
        ]))->getContent();
        $withTime = $this->get(route('app.projects.report.pdf', [
            'project' => $project,
            'arbetstid' => 1,
        ]))->getContent();
        $this->assertIsString($withNotes);
        $this->assertIsString($withTime);
        $this->assertNotSame($plain, $withNotes);
        $this->assertNotSame($plain, $withTime);

        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'photo' => UploadedFile::fake()->image('fore.jpg', 40, 30),
            'moment' => 'before',
        ])->assertRedirect();

        $withPhoto = $this->get(route('app.projects.report.pdf', $project))->getContent();
        $this->assertIsString($withPhoto);
        $this->assertStringStartsWith('%PDF', $withPhoto);
        $this->assertStringContainsString("\xFF\xD8\xFF", $withPhoto);
        $this->assertStringNotContainsString('http://', $withPhoto);
        $this->assertStringNotContainsString('https://', $withPhoto);
        $this->assertNull(EmbeddedImage::jpegDataUri('../secrets.webp', 'sash-photos/'));
        $this->assertNull(EmbeddedImage::jpegDataUri('workshop-logos/1/logo.webp', 'sash-photos/'));
    }

    public function test_another_workshop_cannot_download_the_report(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);

        $this->get(route('app.projects.report.pdf', $project))
            ->assertRedirect(route('login'));

        $this->actingAs($other)
            ->get(route('app.projects.report.pdf', $project))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('app.projects.report.pdf', $project))
            ->assertOk();
    }
}
