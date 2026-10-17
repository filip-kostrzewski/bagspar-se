<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteLine;
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
            ->assertSee('Före · 16 okt 2026', false)
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
            ->assertSee('Visa arbetsanteckningar i rapporten', false)
            ->assertSee('Ladda ner PDF', false)
            ->assertSee('>Skriv ut</button>', false)
            ->assertDontSee('Skriv ut / spara som PDF', false);

        $this->get(route('app.settings.logo'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp');

        $this->get(route('app.projects.report', ['project' => $project, 'anteckningar' => 1]))
            ->assertOk()
            ->assertSee('Arbetsanteckningar', false)
            ->assertSee('kittet löst i nederkant', false)
            ->assertSee('HEMLIG PROJEKTANTECKNING', false)
            ->assertSee('Före · 16 okt 2026', false);
    }

    public function test_report_shows_intake_approval_sash_condition_and_the_quote(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $project->forceFill([
            'intake_approved_at' => Carbon::parse('2026-10-14 09:30:00', 'Europe/Stockholm'),
            'intake_approved_name' => 'Anna Ek',
            'intake_approved_via' => 'link',
        ])->save();
        WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'B-1',
            'intake_notes' => 'Spricka i nedre vänstra hörnet',
        ]);
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'B-2']);
        $quote = Quote::factory()->accepted()->create([
            'project_id' => $project->getKey(),
            'accepted_name' => 'Anna Ek',
        ]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'unit_price_ore' => 400000]);

        $this->actingAs($owner)
            ->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertSee($project->fresh()->intakeApprovalLabel(), false)
            ->assertSee('Skick vid mottagning: Spricka i nedre vänstra hörnet', false)
            ->assertSee('5 000 kr inkl. moms, godkänd', false)
            ->assertSee('av Anna Ek', false)
            ->assertDontSee('inte godkänd', false);
        $this->assertSame(1, substr_count(
            $this->get(route('app.projects.report', $project))->getContent(),
            'Skick vid mottagning',
        ));

        $quote->update(['status' => Quote::STATUS_SENT, 'accepted_at' => null, 'accepted_name' => null]);

        $this->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertSee('Offert skickad', false)
            ->assertSee('inte godkänd', false)
            ->assertDontSee('inkl. moms', false);

        $quote->update(['status' => Quote::STATUS_DRAFT]);

        $this->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertDontSee('Offert skickad', false)
            ->assertDontSee('inkl. moms', false);
    }
}
