<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashTimeEntry;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SashTimeEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_log_time_and_only_the_author_or_owner_can_remove_it(): void
    {
        $owner = User::factory()->create(['name' => 'Ada Ek']);
        $member = User::factory()->member()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Bo Berg',
        ]);
        $otherMember = User::factory()->member()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Cia Ceder',
        ]);
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Villa Ek']);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'V2-H']);
        $foreignProject = Project::factory()->create(['workshop_id' => $other->workshop_id]);
        $foreignSash = WindowSash::factory()->create(['project_id' => $foreignProject->getKey()]);

        $this->actingAs($member)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Spara tid', false);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->post(route('app.sashes.time.store', [$project, $sash]), [
                'form_context' => 'time',
                'worked_on' => '2026-10-16',
                'hours' => '1',
                'minutes' => '30',
                'note' => 'Kittade bågen',
            ])
            ->assertRedirect(route('app.sashes.show', [$project, $sash]))
            ->assertSessionHas('status', 'Tiden har sparats.');

        $entry = SashTimeEntry::query()->firstOrFail();
        $this->assertSame($member->getKey(), $entry->user_id);
        $this->assertSame('Bo Berg', $entry->user_name);
        $this->assertSame(90, $entry->minutes);
        $this->assertSame('2026-10-16', $entry->worked_on?->toDateString());
        $this->assertSame('Kittade bågen', $entry->note);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('1 tim 30 min', false)
            ->assertSee('Bo Berg', false)
            ->assertSee('Kittade bågen', false);

        $this->actingAs($owner)
            ->post(route('app.sashes.time.store', [$project, $sash]), [
                'worked_on' => '2026-10-17',
                'hours' => '0',
                'minutes' => '45',
            ])
            ->assertRedirect();

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Arbetstid', false)
            ->assertSee('2 tim 15 min', false)
            ->assertSee('Bo Berg: 1 tim 30 min', false)
            ->assertSee('Ada Ek: 45 min', false);

        $this->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertDontSee('Arbetstid totalt', false);
        $this->get(route('app.projects.report', $project).'?arbetstid=1')
            ->assertOk()
            ->assertSee('Arbetstid totalt', false)
            ->assertSee('2 tim 15 min', false);

        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('c', 48),
        ])->save();
        $this->get(route('client-status.show', ['token' => $project->client_status_token]))
            ->assertOk()
            ->assertDontSee('Kittade bågen', false)
            ->assertDontSee('Arbetstid', false)
            ->assertDontSee('2 tim 15 min', false);

        $csv = $this->actingAs($owner)->get(route('app.export'))->streamedContent();
        $this->assertStringContainsString('Arbetstid (minuter)', $csv);
        $this->assertStringContainsString('135', $csv);

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->actingAs($member)
            ->post(route('app.sashes.time.store', [$project, $sash]), [
                'form_context' => 'time',
                'worked_on' => '2026-10-16',
                'hours' => '0',
                'minutes' => '0',
            ])
            ->assertRedirect(route('app.sashes.show', [$project, $sash]))
            ->assertInvalid(['hours' => 'Ange tiden i timmar eller minuter.']);

        $this->actingAs($other)
            ->post(route('app.sashes.time.store', [$project, $sash]), [
                'worked_on' => '2026-10-16',
                'minutes' => '10',
            ])
            ->assertNotFound();
        $this->actingAs($member)
            ->post(route('app.sashes.time.store', [$foreignProject, $foreignSash]), [
                'worked_on' => '2026-10-16',
                'minutes' => '10',
            ])
            ->assertNotFound();

        $this->actingAs($otherMember)
            ->delete(route('app.sashes.time.destroy', [$project, $sash, $entry]))
            ->assertForbidden();
        $this->assertModelExists($entry);

        $this->actingAs($other)
            ->delete(route('app.sashes.time.destroy', [$project, $sash, $entry]))
            ->assertNotFound();

        $this->actingAs($member)
            ->delete(route('app.sashes.time.destroy', [$project, $sash, $entry]))
            ->assertRedirect()
            ->assertSessionHas('status', 'Tiden har tagits bort.');
        $this->assertModelMissing($entry);

        $ownerEntry = SashTimeEntry::query()->where('user_id', $owner->getKey())->firstOrFail();
        $this->actingAs($owner)
            ->delete(route('app.sashes.time.destroy', [$project, $sash, $ownerEntry]))
            ->assertRedirect();
        $this->assertModelMissing($ownerEntry);
    }

    public function test_deleting_a_sash_removes_its_time_entries(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $entry = SashTimeEntry::factory()->create([
            'sash_id' => $sash->getKey(),
            'user_id' => $owner->getKey(),
            'user_name' => $owner->name,
        ]);

        $this->actingAs($owner)
            ->delete(route('app.sashes.destroy', [$project, $sash]))
            ->assertRedirect();

        $this->assertModelMissing($sash);
        $this->assertModelMissing($entry);
    }
}
