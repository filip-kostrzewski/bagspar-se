<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteLine;
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
        $this->assertStringContainsString('Projekt;Kund;Plats;Projektstatus;Arkiverad;Båge;Rum;Placering;Etapp;Skapad;Uppdaterad', $csv);
        $this->assertStringContainsString("'=Villa Ek", $csv);
        $this->assertStringContainsString('BRF Eken', $csv);
        $this->assertStringContainsString('V2-H', $csv);
        $this->assertStringContainsString('Glasning', $csv);
        $this->assertStringContainsString('2 okt 2026', $csv);
        $this->assertStringContainsString('16 okt 2026', $csv);
        $this->assertStringNotContainsString('2026-10-02', $csv);
        $this->assertStringNotContainsString('2026-10-16', $csv);
        $this->assertStringNotContainsString('Annat företags projekt', $csv);
        $this->assertStringNotContainsString('Hemlig kund', $csv);
    }

    public function test_the_export_lists_the_quote_status_total_and_acceptance_per_project(): void
    {
        $owner = User::factory()->create();
        $accepted = Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Med offert']);
        $quote = Quote::factory()->accepted()->create([
            'project_id' => $accepted->getKey(),
            'accepted_at' => '2026-10-16 08:30:00',
            'accepted_name' => 'Ada Ek',
        ]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'quantity' => 2, 'unit_price_ore' => 450000]);
        $sent = Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Skickad offert']);
        $sentQuote = Quote::factory()->sent()->create(['project_id' => $sent->getKey()]);
        QuoteLine::factory()->create(['quote_id' => $sentQuote->getKey(), 'unit_price_ore' => 100000]);
        Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Utan offert']);

        $csv = $this->actingAs($owner)->get(route('app.export'))->streamedContent();
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv) ?? $csv;

        $handle = fopen('php://temp', 'w+b');
        $this->assertNotFalse($handle);
        fwrite($handle, $csv);
        rewind($handle);
        $rows = [];
        while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            $rows[$row[0]] = $row;
        }
        fclose($handle);

        $this->assertSame(
            ['Offertstatus', 'Offert exkl. moms', 'Offert godkänd', 'Offert godkänd av'],
            array_slice($rows['Projekt'], -4),
        );
        $this->assertSame(['Godkänd', '9 000 kr', '16 okt 2026 10:30', 'Ada Ek'], array_slice($rows['Med offert'], -4));
        $this->assertSame(['Skickad', '1 000 kr', '', ''], array_slice($rows['Skickad offert'], -4));
        $this->assertSame(['', '', '', ''], array_slice($rows['Utan offert'], -4));
    }
}
