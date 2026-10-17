<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_reads_as_a_workshop_document_with_final_state_and_photo_captions(): void
    {
        Storage::fake('local');
        $this->travelTo(Carbon::parse('2026-10-15 22:30:00', 'UTC'));

        $owner = User::factory()->create();
        $owner->workshop->update([
            'name' => 'Ekens Fönster',
            'organization_number' => '559214-9370',
            'contact_email' => 'verkstad@example.test',
            'address' => 'Storgatan 1, Uppsala',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $project->update(['notes' => 'HEMLIG PROJEKTANTECKNING']);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'B-24',
            'stage' => 'painting',
            'room' => 'Kök',
            'notes' => 'kittet löst i nederkant',
        ]);

        $this->actingAs($owner)
            ->patch(route('app.settings.update'), [
                'name' => 'Ekens Fönster',
                'organization_number' => '559214-9370',
                'contact_email' => 'verkstad@example.test',
                'address' => 'Storgatan 1, Uppsala',
                'logo' => UploadedFile::fake()->image('logo.png', 64, 64),
            ])->assertRedirect();

        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'photo' => UploadedFile::fake()->image('IMG_4481.jpg', 80, 60),
            'moment' => 'before',
        ])->assertRedirect();

        $photo = SashPhoto::query()->firstOrFail();

        $report = $this->get(route('app.projects.report', $project));

        $report->assertOk()
            ->assertSee('class="report-workshop__name">Ekens Fönster</p>', false)
            ->assertSee('Org.nr 559214-9370', false)
            ->assertSee('verkstad@example.test', false)
            ->assertSee('Storgatan 1, Uppsala', false)
            ->assertSee(route('app.settings.logo'), false)
            ->assertSee('<img src="'.route('app.sashes.photos.show', [$project, $sash, $photo]).'"', false)
            ->assertSee('Före · 2026-10-16', false)
            ->assertSee('Målning', false)
            ->assertSee('class="report-footer__product">Bågspår</span>', false)
            ->assertDontSee('IMG_4481.jpg', false)
            ->assertDontSee('stage-track', false)
            ->assertDontSee('Skrapning', false)
            ->assertDontSee('Glasning', false)
            ->assertDontSee('report-header__brand', false)
            ->assertDontSee('Pågår', false)
            ->assertDontSee('Avslutat', false)
            ->assertDontSee('kittet löst i nederkant', false)
            ->assertDontSee('HEMLIG PROJEKTANTECKNING', false)
            ->assertDontSee('Arbetsanteckningar', false)
            ->assertSee('Visa arbetsanteckningar i rapporten', false);

        $this->get(route('app.settings.logo'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp');

        $this->get(route('app.projects.report', ['project' => $project, 'anteckningar' => 1]))
            ->assertOk()
            ->assertSee('Arbetsanteckningar', false)
            ->assertSee('kittet löst i nederkant', false)
            ->assertSee('HEMLIG PROJEKTANTECKNING', false)
            ->assertSee('Före · 2026-10-16', false);
    }
}
