<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashMaterial;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SashMaterialTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_record_materials_and_print_the_project_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $other = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'V2-H']);
        $otherSash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'K1']);
        $foreignProject = Project::factory()->create(['workshop_id' => $other->workshop_id]);
        $foreignSash = WindowSash::factory()->create(['project_id' => $foreignProject->getKey()]);

        $this->actingAs($member)
            ->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.materials.store', [$project, $sash]), [
                'form_context' => 'material',
                'type' => 'glas',
                'description' => 'Energiglas',
                'quantity' => '1,5',
                'unit' => 'm²',
            ])
            ->assertRedirect(route('app.sashes.show', [$project, $sash]))
            ->assertSessionHas('status', 'Materialet har sparats.');

        $this->actingAs($owner)
            ->post(route('app.sashes.materials.store', [$project, $sash]), [
                'type' => 'glas',
                'description' => ' energiglas ',
                'quantity' => '0.5',
                'unit' => 'm²',
            ])
            ->assertRedirect();
        $this->post(route('app.sashes.materials.store', [$project, $otherSash]), [
            'type' => 'kitt',
            'description' => 'Linoljekitt',
            'quantity' => '2',
            'unit' => 'kg',
        ])->assertRedirect();

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Material på den här bågen', false)
            ->assertSee('2 rader på den här bågen.', false)
            ->assertSee('data-confirm-title="Ta bort materialet?"', false)
            ->assertSee('Energiglas', false)
            ->assertSee('1,5 m²', false)
            ->assertSee('Material lades till: Glas, Energiglas, 1,5 m².', false);

        $sheet = $this->get(route('app.projects.materials', $project));
        $sheet->assertOk()
            ->assertSee('Energiglas', false)
            ->assertSee('Glas', false)
            ->assertSee('Linoljekitt', false)
            ->assertSee('kg', false)
            ->assertDontSee('1,5', false)
            ->assertDontSee('0,5', false);

        $this->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertDontSee('Energiglas', false)
            ->assertDontSee('Linoljekitt', false);

        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('d', 48),
        ])->save();
        $this->get(route('client-status.show', ['token' => $project->client_status_token]))
            ->assertOk()
            ->assertDontSee('Energiglas', false)
            ->assertDontSee('Linoljekitt', false);

        $csv = $this->get(route('app.export'))->streamedContent();
        $this->assertStringContainsString('Material', $csv);
        $this->assertStringContainsString('Glas: 1,5 m² Energiglas', $csv);
        $this->assertStringContainsString('Glas: 0,5 m² energiglas', $csv);
        $this->assertStringContainsString('Kitt: 2 kg Linoljekitt', $csv);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.materials.store', [$project, $sash]), [
                'form_context' => 'material',
                'type' => 'okänd',
                'description' => '',
                'quantity' => '0',
                'unit' => '',
            ])
            ->assertRedirect(route('app.sashes.show', [$project, $sash]))
            ->assertInvalid([
                'type' => 'Välj glas, kitt, färg, beslag eller övrigt.',
                'description' => 'Ange en beskrivning.',
                'quantity' => 'Mängden måste vara ett positivt tal.',
                'unit' => 'Ange en enhet.',
            ]);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->followingRedirects()
            ->post(route('app.sashes.materials.store', [$project, $sash]), [
                'form_context' => 'material',
                'type' => 'glas',
                'description' => 'Fel mängd',
                'quantity' => '-1',
                'unit' => 'm²',
            ])
            ->assertOk()
            ->assertSee('Mängden måste vara ett positivt tal.', false)
            ->assertDontSee('The quantity field must be at least 0.01.', false);

        $this->actingAs($other)
            ->post(route('app.sashes.materials.store', [$project, $sash]), [
                'type' => 'färg',
                'description' => 'Linoljefärg',
                'quantity' => '1',
                'unit' => 'liter',
            ])
            ->assertNotFound();
        $this->get(route('app.projects.materials', $project))->assertNotFound();

        $material = SashMaterial::query()->where('description', 'Energiglas')->firstOrFail();
        $this->actingAs($member)
            ->delete(route('app.sashes.materials.destroy', [$project, $sash, $material]))
            ->assertRedirect()
            ->assertSessionHas('status', 'Materialraden har tagits bort.');
        $this->assertModelMissing($material);
        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Material togs bort: Glas, Energiglas, 1,5 m².', false);

        $this->actingAs($other)
            ->delete(route('app.sashes.materials.destroy', [$project, $otherSash, SashMaterial::query()->firstOrFail()]))
            ->assertNotFound();
        $this->actingAs($member)
            ->post(route('app.sashes.materials.store', [$foreignProject, $foreignSash]), [
                'type' => 'beslag',
                'description' => 'Gångjärn',
                'quantity' => '4',
                'unit' => 'st',
            ])
            ->assertNotFound();
    }

    public function test_a_member_can_edit_a_material_row_and_the_change_is_logged(): void
    {
        $owner = User::factory()->create(['name' => 'Ada Ek']);
        $member = User::factory()->member()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Bo Berg',
        ]);
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $material = SashMaterial::factory()->create([
            'sash_id' => $sash->getKey(),
            'type' => 'glas',
            'description' => 'Energiglas',
            'quantity' => '1.50',
            'unit' => 'm²',
        ]);

        $this->actingAs($other)
            ->patch(route('app.sashes.materials.update', [$project, $sash, $material]), [
                'type' => 'kitt',
                'description' => 'Linoljekitt',
                'quantity' => '2',
                'unit' => 'kg',
            ])
            ->assertNotFound();
        $this->assertSame('Energiglas', $material->refresh()->description);

        $this->actingAs($member)
            ->patch(route('app.sashes.materials.update', [$project, $sash, $material]), [
                'type' => 'kitt',
                'description' => 'Linoljekitt',
                'quantity' => '2,5',
                'unit' => 'kg',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Materialet har ändrats.');

        $material->refresh();
        $this->assertSame('kitt', $material->type);
        $this->assertSame('Linoljekitt', $material->description);
        $this->assertSame('2.50', $material->quantity);
        $this->assertSame('kg', $material->unit);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Material ändrades: Glas, Energiglas, 1,5 m² blev Kitt, Linoljekitt, 2,5 kg.', false)
            ->assertSee('1 rad på den här bågen.', false);
    }

    public function test_deleting_a_sash_removes_its_materials(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $material = SashMaterial::factory()->create(['sash_id' => $sash->getKey()]);

        $this->actingAs($owner)
            ->delete(route('app.sashes.destroy', [$project, $sash]))
            ->assertRedirect();

        $this->assertModelMissing($sash);
        $this->assertModelMissing($material);
    }
}
