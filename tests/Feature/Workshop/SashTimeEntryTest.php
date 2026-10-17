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
            ->assertSee('Totalt 1 tim 30 min på den här bågen.', false)
            ->assertSee('data-confirm-title="Ta bort tidsposten?"', false)
            ->assertSee('Tid lades till: 16 okt 2026, 1 tim 30 min. Kittade bågen.', false)
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
            ->assertSee('2 tim 15 min', false)
            ->assertSee('Bo Berg: 1 tim 30 min', false)
            ->assertSee('Ada Ek: 45 min', false);

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

        $this->from(route('app.sashes.show', [$project, $sash]))
            ->actingAs($member)
            ->followingRedirects()
            ->post(route('app.sashes.time.store', [$project, $sash]), [
                'form_context' => 'time',
                'worked_on' => '2026-10-16',
                'hours' => '-2',
                'minutes' => '10',
            ])
            ->assertOk()
            ->assertSee('Timmarna kan inte vara negativa.', false)
            ->assertDontSee('The hours field must be at least 0.', false);

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
        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Tid togs bort: 16 okt 2026, 1 tim 30 min. Kittade bågen.', false);

        $ownerEntry = SashTimeEntry::query()->where('user_id', $owner->getKey())->firstOrFail();
        $this->actingAs($owner)
            ->delete(route('app.sashes.time.destroy', [$project, $sash, $ownerEntry]))
            ->assertRedirect();
        $this->assertModelMissing($ownerEntry);
    }

    public function test_the_author_can_edit_a_time_entry_and_the_change_is_logged(): void
    {
        $owner = User::factory()->create(['name' => 'Ada Ek']);
        $member = User::factory()->member()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Bo Berg',
        ]);
        $otherMember = User::factory()->member()->create([
            'workshop_id' => $owner->workshop_id,
        ]);
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $entry = SashTimeEntry::factory()->create([
            'sash_id' => $sash->getKey(),
            'user_id' => $member->getKey(),
            'user_name' => 'Bo Berg',
            'worked_on' => '2026-10-16',
            'minutes' => 90,
            'note' => 'Kittade bågen',
        ]);

        $this->actingAs($otherMember)
            ->patch(route('app.sashes.time.update', [$project, $sash, $entry]), [
                'worked_on' => '2026-10-16',
                'hours' => '2',
                'minutes' => '0',
            ])
            ->assertForbidden();
        $this->assertSame(90, $entry->refresh()->minutes);

        $this->actingAs($other)
            ->patch(route('app.sashes.time.update', [$project, $sash, $entry]), [
                'worked_on' => '2026-10-16',
                'hours' => '2',
            ])
            ->assertNotFound();

        $this->actingAs($member)
            ->patch(route('app.sashes.time.update', [$project, $sash, $entry]), [
                'worked_on' => '2026-10-17',
                'hours' => '2',
                'minutes' => '0',
                'note' => 'Ommätt',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Tiden har ändrats.');

        $entry->refresh();
        $this->assertSame($member->getKey(), $entry->user_id);
        $this->assertSame('Bo Berg', $entry->user_name);
        $this->assertSame(120, $entry->minutes);
        $this->assertSame('2026-10-17', $entry->worked_on?->toDateString());
        $this->assertSame('Ommätt', $entry->note);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Tid ändrades: 16 okt 2026, 1 tim 30 min. Kittade bågen blev 17 okt 2026, 2 tim. Ommätt.', false)
            ->assertSee('2 tim', false);
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
