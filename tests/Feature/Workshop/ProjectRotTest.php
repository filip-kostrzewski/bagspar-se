<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashMaterial;
use App\Models\User;
use App\Models\WindowSash;
use App\Support\RotUnderlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRotTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_underlag_uses_the_workshops_prices_and_leaves_out_unpriced_rows(): void
    {
        $owner = User::factory()->create(['name' => 'Ada Ek']);
        $member = User::factory()->member()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Bo Berg',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'V2-H',
        ]);
        $otherSash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'K1',
        ]);

        $this->actingAs($member)
            ->patch(route('app.settings.update'), [
                'name' => 'Annat namn',
                'hourly_rate' => '100',
            ])
            ->assertForbidden();
        $this->assertNull($owner->workshop->fresh()->hourly_rate_ore);

        $this->actingAs($owner)
            ->from(route('app.settings.edit'))
            ->patch(route('app.settings.update'), [
                'name' => $owner->workshop->name,
                'hourly_rate' => '650',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Verkstadens uppgifter har sparats.');
        $this->assertSame(65000, $owner->workshop->fresh()->hourly_rate_ore);

        $this->actingAs($member)
            ->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.materials.store', [$project, $sash]), [
                'form_context' => 'material',
                'type' => 'glas',
                'description' => 'Energiglas',
                'quantity' => '2',
                'unit' => 'm²',
                'unit_price' => '800,50.1',
            ])
            ->assertRedirect(route('app.sashes.show', [$project, $sash]))
            ->assertSessionHasErrors([
                'unit_price' => 'Ange à-priset i kronor, till exempel 800 eller 800,50.',
            ]);
        $this->assertSame(0, SashMaterial::query()->count());

        $this->post(route('app.sashes.materials.store', [$project, $sash]), [
            'form_context' => 'material',
            'type' => 'glas',
            'description' => 'Energiglas',
            'quantity' => '2',
            'unit' => 'm²',
            'unit_price' => '800',
            'unit_price_ore' => 1,
        ])->assertRedirect();
        $this->post(route('app.sashes.materials.store', [$project, $otherSash]), [
            'form_context' => 'material',
            'type' => 'kitt',
            'description' => 'Linoljekitt',
            'quantity' => '1',
            'unit' => 'kg',
            'unit_price' => '',
        ])->assertRedirect();
        $this->post(route('app.sashes.time.store', [$project, $sash]), [
            'worked_on' => '2026-10-16',
            'hours' => '1',
            'minutes' => '30',
            'note' => 'Kittade bågen',
        ])->assertRedirect();

        $priced = SashMaterial::query()->where('description', 'Energiglas')->firstOrFail();
        $this->assertSame(80000, $priced->unit_price_ore);

        $page = $this->get(route('app.projects.rot', $project));
        $page->assertOk()
            ->assertSee(RotUnderlag::HEADING, false)
            ->assertSee(RotUnderlag::BODY, false)
            ->assertSee('Timpris 650 kr.', false)
            ->assertSee('Ägaren ändrar timpriset under verkstadens uppgifter.', false)
            ->assertDontSee('>Ändra timpriset</a>', false)
            ->assertSee('Totalt 1 tim 30 min, 975 kr.', false)
            ->assertSee('Bo Berg', false)
            ->assertSee('Kittade bågen', false)
            ->assertSee('16 okt 2026', false)
            ->assertSee('Energiglas', false)
            ->assertSee('800 kr', false)
            ->assertSee('1 600 kr', false)
            ->assertSee('Linoljekitt', false)
            ->assertSee('Pris saknas', false)
            ->assertSee('1 rad saknar pris och ingår inte i materialsumman.', false)
            ->assertSee('Material 1 600 kr.', false)
            ->assertDontSee('30 %', false)
            ->assertDontSee('30%', false);

        $this->actingAs($owner)
            ->get(route('app.projects.rot', $project))
            ->assertOk()
            ->assertSee('Ändra timpriset', false);

        $csv = $this->actingAs($member)->get(route('app.projects.rot.csv', $project));
        $csv->assertOk();
        $csv->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = (string) $csv->headers->get('Content-Disposition');
        $this->assertStringContainsString('villa-ek-rot-underlag.csv', $disposition);
        $body = $csv->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertStringContainsString(RotUnderlag::HEADING.'. '.RotUnderlag::BODY, $body);
        $this->assertStringContainsString('Arbetstid;"16 okt 2026";V2-H;"Bo Berg";"1 tim 30 min";"Kittade bågen";650;975', $body);
        $this->assertStringContainsString('Material;;V2-H;"Glas, Energiglas";"2 m²";;800;"1 600"', $body);
        $this->assertStringContainsString('Material;;K1;"Kitt, Linoljekitt";"1 kg";"Pris saknas";;', $body);
        $this->assertStringContainsString('Summering;;;Arbetskostnad;"1 tim 30 min";;;975', $body);
        $this->assertStringContainsString('Summering;;;Material;;"Rader utan pris ingår inte";;"1 600"', $body);
        $this->assertStringNotContainsString('30%', $body);

        $pdf = $this->get(route('app.projects.rot.pdf', $project));
        $pdf->assertOk();
        $pdf->assertHeader('Content-Type', 'application/pdf');
        $pdf->assertHeader('Content-Disposition', 'attachment; filename="villa-ek-rot-underlag.pdf"');
        $pdfBody = $pdf->getContent();
        $this->assertIsString($pdfBody);
        $this->assertStringStartsWith('%PDF', $pdfBody);
    }

    public function test_time_is_shown_when_the_workshop_has_no_hourly_rate(): void
    {
        $owner = User::factory()->create(['name' => 'Ada Ek']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Villa Ek']);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'V2-H']);

        $this->actingAs($owner)
            ->post(route('app.sashes.time.store', [$project, $sash]), [
                'worked_on' => '2026-10-16',
                'hours' => '1',
                'minutes' => '0',
            ])
            ->assertRedirect();

        $this->get(route('app.projects.rot', $project))
            ->assertOk()
            ->assertSee('Inget timpris är sparat.', false)
            ->assertSee('Totalt 1 tim.', false)
            ->assertSee('Timpris saknas', false)
            ->assertDontSee('975 kr', false);
    }

    public function test_another_workshop_cannot_read_the_underlag(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->get(route('app.projects.rot', $project))->assertRedirect(route('login'));
        $this->get(route('app.projects.rot.csv', $project))->assertRedirect(route('login'));
        $this->get(route('app.projects.rot.pdf', $project))->assertRedirect(route('login'));

        $this->actingAs($other)
            ->get(route('app.projects.rot', $project))
            ->assertNotFound();
        $this->get(route('app.projects.rot.csv', $project))->assertNotFound();
        $this->get(route('app.projects.rot.pdf', $project))->assertNotFound();
    }
}
