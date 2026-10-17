<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectListFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_project_list_filters_by_customer_room_stage_and_archive(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $kitchen = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'customer_name' => 'BRF Eken',
        ]);
        $hall = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Björk',
            'customer_name' => 'BRF Björken',
        ]);
        $archived = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Arkiverad villa',
            'customer_name' => 'BRF Eken',
            'archived_at' => now(),
        ]);
        Project::factory()->create([
            'workshop_id' => $other->workshop_id,
            'name' => 'Annat företags projekt',
            'customer_name' => 'BRF Eken',
        ]);
        WindowSash::factory()->create([
            'project_id' => $kitchen->getKey(),
            'room' => 'Kök',
            'stage' => 'glazing',
        ]);
        WindowSash::factory()->create([
            'project_id' => $hall->getKey(),
            'room' => 'Hall',
            'stage' => 'painting',
        ]);
        WindowSash::factory()->create([
            'project_id' => $archived->getKey(),
            'room' => 'Kök',
            'stage' => 'ready',
        ]);

        $this->actingAs($owner)->get(route('app.dashboard', ['kund' => 'Eken']))
            ->assertOk()
            ->assertSee('Villa Ek', false)
            ->assertDontSee('Villa Björk', false)
            ->assertDontSee('Arkiverad villa', false)
            ->assertDontSee('Annat företags projekt', false);

        $this->get(route('app.dashboard', ['kund' => '%']))
            ->assertOk()
            ->assertDontSee('Villa Ek', false)
            ->assertSee('Inga projekt matchar sökningen.', false);

        $this->get(route('app.dashboard', ['rum' => 'Kök', 'etapp' => 'glazing']))
            ->assertOk()
            ->assertSee('Villa Ek', false)
            ->assertDontSee('Villa Björk', false);

        $this->get(route('app.dashboard', ['etapp' => 'not-a-stage']))
            ->assertOk()
            ->assertSee('Villa Ek', false)
            ->assertSee('Villa Björk', false);

        $this->get(route('app.dashboard', ['kund' => 'Eken', 'arkiv' => 1]))
            ->assertOk()
            ->assertSee('Arkiverad villa', false)
            ->assertDontSee('Villa Ek', false);
    }
}
