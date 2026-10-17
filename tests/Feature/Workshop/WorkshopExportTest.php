<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_owner_can_download_projects_sashes_stages_and_dates(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $other = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => '=Villa Ek',
            'customer_name' => 'BRF Eken',
            'location' => 'Våning 2',
            'status' => 'active',
        ]);
        $project->forceFill([
            'created_at' => '2026-10-02 08:00:00',
            'updated_at' => '2026-10-02 08:00:00',
        ])->save();
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'V2-H',
            'room' => 'Kök',
            'location' => 'Höger',
            'stage' => 'glazing',
        ]);
        $sash->forceFill([
            'created_at' => '2026-10-02 09:00:00',
            'updated_at' => '2026-10-16 09:00:00',
        ])->save();
        Project::factory()->create([
            'workshop_id' => $other->workshop_id,
            'name' => 'Annat företags projekt',
            'customer_name' => 'Hemlig kund',
        ]);

        $this->actingAs($member)->get(route('app.export'))->assertForbidden();
        $this->actingAs($other)->get(route('app.export'))->assertOk()->assertDontSee('Villa Ek', false)->assertDontSee('Hemlig kund', false);

        $response = $this->actingAs($owner)->get(route('app.export'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Projekt,Kund,Plats,Projektstatus,Arkiverad,Båge,Rum,Placering,Etapp,Skapad,Uppdaterad', $csv);
        $this->assertStringContainsString("'=Villa Ek", $csv);
        $this->assertStringContainsString('BRF Eken', $csv);
        $this->assertStringContainsString('V2-H', $csv);
        $this->assertStringContainsString('Glasning', $csv);
        $this->assertStringContainsString('2026-10-02', $csv);
        $this->assertStringContainsString('2026-10-16', $csv);
        $this->assertStringNotContainsString('Annat företags projekt', $csv);
        $this->assertStringNotContainsString('Hemlig kund', $csv);
    }
}
