<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WindowSashControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_workshop_owner_can_open_add_sash_page_and_project_cta_links_to_it(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee(route('app.sashes.create', $project), false)
            ->assertDontSee('id="new-sash"', false);

        $this->get(route('app.sashes.create', $project))
            ->assertOk()
            ->assertViewIs('app.sashes.create')
            ->assertSee('Lägg till en båge')
            ->assertSee('name="code"', false)
            ->assertSee('autofocus', false)
            ->assertSee('required', false)
            ->assertSee('name="room"', false)
            ->assertSee('name="location"', false)
            ->assertSee('name="notes"', false)
            ->assertSee('Avbryt');
    }

    public function test_guest_is_redirected_to_login_from_add_sash_page(): void
    {
        $project = Project::factory()->create();

        $this->get(route('app.sashes.create', $project))
            ->assertRedirect(route('login'));
    }

    public function test_workshop_owner_cannot_open_add_sash_page_for_another_workshop_project(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $otherProject = Project::factory()->create(['workshop_id' => $otherOwner->workshop_id]);

        $this->actingAs($owner)
            ->get(route('app.sashes.create', $otherProject))
            ->assertNotFound();
    }

    public function test_valid_sash_form_creates_workcard_and_redirects_to_saved_card(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $response = $this->actingAs($owner)
            ->from(route('app.sashes.create', $project))
            ->post(route('app.sashes.store', $project), [
                'code' => 'B-24',
                'room' => 'Kök',
                'location' => 'Vänster innerbåge',
                'notes' => 'Bevara originalglaset.',
            ]);

        $sash = $project->windowSashes()->firstOrFail();

        $response->assertRedirect(route('app.sashes.show', [$project, $sash]))
            ->assertSessionHas('status', 'Sparat');

        $this->assertDatabaseHas('window_sashes', [
            'id' => $sash->getKey(),
            'project_id' => $project->getKey(),
            'code' => 'B-24',
            'room' => 'Kök',
            'location' => 'Vänster innerbåge',
            'stage' => 'received',
            'notes' => 'Bevara originalglaset.',
        ]);

        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Båge B-24')
            ->assertSee('Sparat');
    }

    public function test_missing_sash_code_returns_to_form_with_validation_message(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->from(route('app.sashes.create', $project))
            ->followingRedirects()
            ->post(route('app.sashes.store', $project), [
                'room' => 'Kök',
                'location' => 'Vänster innerbåge',
                'notes' => 'Spara dessa uppgifter.',
            ])
            ->assertOk()
            ->assertSee('Ange en kod för bågen.')
            ->assertSee('value="Kök"', false)
            ->assertSee('value="Vänster innerbåge"', false)
            ->assertSee('Spara dessa uppgifter.');

        $this->assertDatabaseCount('window_sashes', 0);
    }

    public function test_duplicate_sash_code_returns_to_form_with_error_and_preserves_input(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'B-24',
        ]);

        $this->actingAs($owner)
            ->from(route('app.sashes.create', $project))
            ->followingRedirects()
            ->post(route('app.sashes.store', $project), [
                'code' => 'B-24',
                'room' => 'Sovrum',
                'location' => 'Höger ytterbåge',
                'notes' => 'Korrigera koden innan sparning.',
            ])
            ->assertOk()
            ->assertSee('Koden används redan i det här projektet.')
            ->assertSee('value="B-24"', false)
            ->assertSee('value="Sovrum"', false)
            ->assertSee('value="Höger ytterbåge"', false)
            ->assertSee('Korrigera koden innan sparning.');

        $this->assertDatabaseCount('window_sashes', 1);
    }
}
