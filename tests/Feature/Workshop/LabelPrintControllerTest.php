<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelPrintControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sash_page_links_to_a_label_view_with_only_qr_code_room_and_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'B-24',
            'room' => 'Kök',
            'location' => 'Vänster innerbåge',
        ]);
        $otherSash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'B-25',
            'room' => 'Hall',
        ]);

        $this->actingAs($owner)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee(route('app.sashes.label', [$project, $sash]), false)
            ->assertDontSee('workshop-label-print', false)
            ->assertDontSee('data-print-label', false);

        $label = $this->get(route('app.sashes.label', [$project, $sash]));

        $label->assertOk()
            ->assertSee('label-print-standalone label-print-single', false)
            ->assertSee('@page {', false)
            ->assertSee('size: 62mm 29mm;', false)
            ->assertSee('margin: 0;', false)
            ->assertSee('class="print-label"', false)
            ->assertSee(route('app.sashes.qr', [$project, $sash]), false)
            ->assertSee('B-24', false)
            ->assertSee('Kök', false)
            ->assertSee('Villa Ek', false)
            ->assertSee('Skanningen är till för verkstadens personal och kräver inloggning.', false)
            ->assertSee(route('app.projects.labels', $project), false)
            ->assertDontSee('B-25', false)
            ->assertDontSee('Vänster innerbåge', false)
            ->assertDontSee('<form', false)
            ->assertDontSee(route('app.sashes.show', [$project, $otherSash]), false);

        $css = file_get_contents(public_path('css/workshop.css'));
        $this->assertIsString($css);
        $this->assertStringContainsString('size: 62mm 29mm', $css);
        $this->assertStringContainsString('margin: 0', $css);
        $this->assertStringContainsString('page: label-62x29', $css);
    }

    public function test_project_sheet_prints_a_grid_of_62_by_29_labels_for_every_sash(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Brf Eken',
        ]);
        WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'A-01',
            'room' => 'Sovrum',
        ]);
        WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'A-02',
            'room' => 'Vardagsrum',
        ]);

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee(route('app.projects.labels', $project), false);

        $sheet = $this->get(route('app.projects.labels', $project));

        $sheet->assertOk()
            ->assertSee('label-print-standalone label-print-sheet', false)
            ->assertSee('class="label-sheet"', false)
            ->assertSee('A-01', false)
            ->assertSee('Sovrum', false)
            ->assertSee('A-02', false)
            ->assertSee('Vardagsrum', false)
            ->assertSee('Brf Eken', false)
            ->assertDontSee('<form', false);

        $css = file_get_contents(public_path('css/workshop.css'));
        $this->assertIsString($css);
        $this->assertStringContainsString('size: A4 portrait', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(3, 62mm)', $css);
        $this->assertStringContainsString('grid-auto-rows: 29mm', $css);
    }

    public function test_empty_project_sheet_explains_that_there_are_no_labels(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->get(route('app.projects.labels', $project))
            ->assertOk()
            ->assertSee('Det finns inga bågar att skriva ut etiketter för ännu.', false)
            ->assertDontSee('class="print-label"', false);
    }

    public function test_guest_and_other_workshop_cannot_open_label_pages(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);

        $this->get(route('app.sashes.label', [$project, $sash]))->assertRedirect(route('login'));
        $this->get(route('app.projects.labels', $project))->assertRedirect(route('login'));

        $this->actingAs($otherOwner)
            ->get(route('app.sashes.label', [$project, $sash]))
            ->assertNotFound();

        $this->get(route('app.projects.labels', $project))->assertNotFound();
    }
}
