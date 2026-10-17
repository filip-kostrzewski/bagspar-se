<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashEvent;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SashHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_sash_records_the_opening_stage_and_a_note_when_one_is_written(): void
    {
        $owner = User::factory()->create(['name' => 'Anna Andersson']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)->post(route('app.sashes.store', $project), [
            'code' => 'B-24',
            'room' => 'Kök',
            'notes' => 'Behåll originalglaset.',
        ])->assertRedirect();

        $sash = WindowSash::query()->where('code', 'B-24')->firstOrFail();

        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'user_id' => $owner->getKey(),
            'user_name' => 'Anna Andersson',
            'kind' => SashEvent::KIND_STAGE,
            'stage' => 'received',
        ]);
        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'kind' => SashEvent::KIND_NOTE,
            'body' => 'Behåll originalglaset.',
        ]);
        $this->assertSame(2, $sash->events()->count());
    }

    public function test_a_sash_without_a_note_records_only_the_opening_stage(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)->post(route('app.sashes.store', $project), [
            'code' => 'B-25',
            'room' => 'Hall',
        ])->assertRedirect();

        $sash = WindowSash::query()->where('code', 'B-25')->firstOrFail();

        $this->assertSame(1, $sash->events()->count());
        $this->assertSame(SashEvent::KIND_STAGE, $sash->events()->firstOrFail()->kind);
    }

    public function test_stage_and_note_changes_are_appended_and_shown_with_the_author(): void
    {
        $this->travelTo(Carbon::parse('2026-10-16 08:15:00', 'UTC'));

        $owner = User::factory()->create(['name' => 'Anna Andersson']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)->post(route('app.sashes.store', $project), [
            'code' => 'B-24',
            'room' => 'Kök',
            'notes' => 'Behåll originalglaset.',
        ])->assertRedirect();

        $sash = WindowSash::query()->where('code', 'B-24')->firstOrFail();
        $openingStage = $sash->events()->where('kind', SashEvent::KIND_STAGE)->firstOrFail();

        $this->travelTo(Carbon::parse('2026-10-16 10:00:00', 'UTC'));
        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'stage' => 'scraping',
        ])->assertRedirect();

        $openingStage->refresh();
        $this->assertSame('received', $openingStage->stage);
        $this->assertTrue($openingStage->created_at?->equalTo(Carbon::parse('2026-10-16 08:15:00', 'UTC')));
        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'kind' => SashEvent::KIND_STAGE,
            'stage' => 'scraping',
        ]);

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'notes' => 'Nytt kitt i nederstycket.',
        ])->assertRedirect();

        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'kind' => SashEvent::KIND_NOTE,
            'body' => 'Nytt kitt i nederstycket.',
        ]);

        $eventCount = SashEvent::query()->count();
        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'room' => 'Hall',
        ])->assertRedirect();
        $this->assertSame($eventCount, SashEvent::query()->count());

        $this->patch(route('app.sashes.update', [$project, $sash]), [
            'notes' => '',
        ])->assertRedirect();

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Historik', false)
            ->assertSee('Anna Andersson', false)
            ->assertSee('2026-10-16 10:15', false)
            ->assertSee('2026-10-16 12:00', false)
            ->assertSee('Mottagen', false)
            ->assertSee('Skrapning', false)
            ->assertSee('Nytt kitt i nederstycket.', false)
            ->assertSee('Anteckningen togs bort.', false);
    }
}
