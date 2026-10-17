<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashEvent;
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

    public function test_bulk_form_is_on_the_add_page_and_linked_from_the_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Lägg till flera')
            ->assertSee('flera=1', false);

        $this->get(route('app.sashes.create', $project))
            ->assertOk()
            ->assertSee('Lägg till flera bågar')
            ->assertSee(route('app.sashes.store-many', $project), false)
            ->assertSee('name="lines"', false)
            ->assertSee('2-KÖK-F1-B1; Kök; Vänster ytterbåge');
    }

    public function test_bulk_create_adds_all_sashes_with_rooms_and_locations_and_records_events(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->post(route('app.sashes.store-many', $project), [
                'lines' => "2-KÖK-F1-B1; Kök; Vänster ytterbåge\n  2-KÖK-F1-B2 ;Kök;  \nB-3\r\nB-4; ; Höger\n",
            ])
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHas('status', '4 bågar tillagda.');

        $this->assertDatabaseCount('window_sashes', 4);
        $this->assertDatabaseHas('window_sashes', [
            'project_id' => $project->getKey(),
            'code' => '2-KÖK-F1-B1',
            'room' => 'Kök',
            'location' => 'Vänster ytterbåge',
            'stage' => 'received',
        ]);
        $this->assertDatabaseHas('window_sashes', ['code' => '2-KÖK-F1-B2', 'room' => 'Kök', 'location' => null]);
        $this->assertDatabaseHas('window_sashes', ['code' => 'B-3', 'room' => null, 'location' => null]);
        $this->assertDatabaseHas('window_sashes', ['code' => 'B-4', 'room' => null, 'location' => 'Höger']);
        $this->assertSame(4, SashEvent::query()->where('kind', SashEvent::KIND_STAGE)->where('user_id', $owner->getKey())->count());

        $this->get(route('app.projects.show', $project))->assertSee('4 bågar tillagda.');
    }

    public function test_bulk_create_skips_blank_lines_and_says_singular_for_one_sash(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->post(route('app.sashes.store-many', $project), ['lines' => "\n   \n\nA-1\n\t\n"])
            ->assertSessionHas('status', '1 båge tillagd.');

        $this->assertDatabaseCount('window_sashes', 1);
    }

    public function test_bulk_create_rejects_a_duplicate_inside_the_list_naming_the_line(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->from(route('app.sashes.create', $project))
            ->post(route('app.sashes.store-many', $project), ['lines' => "A-1\n\nB-2\na-1; Kök"])
            ->assertRedirect(route('app.sashes.create', $project))
            ->assertSessionHasErrors(['lines' => 'Rad 4: koden a-1 finns redan på rad 1.']);

        $this->assertDatabaseCount('window_sashes', 0);
    }

    public function test_bulk_create_rejects_a_code_that_already_exists_in_the_project_but_allows_it_in_another(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $other = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'B-24']);
        WindowSash::factory()->create(['project_id' => $other->getKey(), 'code' => 'C-1']);

        $this->actingAs($owner)
            ->from(route('app.sashes.create', $project))
            ->post(route('app.sashes.store-many', $project), ['lines' => "NY-1\nB-24; Kök"])
            ->assertSessionHasErrors(['lines' => 'Rad 2: koden B-24 används redan i det här projektet.']);

        $this->assertDatabaseCount('window_sashes', 2);

        $this->post(route('app.sashes.store-many', $project), ['lines' => 'C-1'])
            ->assertSessionHas('status', '1 båge tillagd.');
    }

    public function test_bulk_create_rejects_the_201st_line_and_creates_nothing(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $codes = array_map(fn (int $number): string => 'B-'.$number, range(1, 201));

        $this->actingAs($owner)
            ->from(route('app.sashes.create', $project))
            ->post(route('app.sashes.store-many', $project), ['lines' => implode("\n", $codes)])
            ->assertSessionHasErrors('lines');

        $this->assertDatabaseCount('window_sashes', 0);

        $this->post(route('app.sashes.store-many', $project), ['lines' => implode("\n", array_slice($codes, 0, 200))])
            ->assertSessionHas('status', '200 bågar tillagda.');

        $this->assertDatabaseCount('window_sashes', 200);
    }

    public function test_bulk_create_rejects_too_long_fields_and_empty_input_and_creates_nothing(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $this->actingAs($owner)->from(route('app.sashes.create', $project));

        $this->post(route('app.sashes.store-many', $project), [
            'lines' => "OK-1\n".str_repeat('k', 121)."\nOK-2; ".str_repeat('r', 121)."\nOK-3; Kök; ".str_repeat('p', 161)."\n; Kök\nA; B; C; D",
        ])->assertSessionHasErrors('lines');

        $this->assertSame([
            'Rad 2: koden får vara högst 120 tecken.',
            'Rad 3: rummet får vara högst 120 tecken.',
            'Rad 4: placeringen får vara högst 160 tecken.',
            'Rad 5: koden saknas.',
            'Rad 6: använd högst två semikolon (kod; rum; placering).',
        ], session('errors')->get('lines'));

        $this->post(route('app.sashes.store-many', $project), ['lines' => "  \n \n"])
            ->assertSessionHasErrors(['lines' => 'Skriv minst en båge, en per rad.']);

        $this->post(route('app.sashes.store-many', $project), [])
            ->assertSessionHasErrors(['lines' => 'Skriv minst en båge, en per rad.']);

        $this->assertDatabaseCount('window_sashes', 0);
        $this->assertDatabaseCount('sash_events', 0);
    }

    public function test_bulk_create_shows_line_errors_on_the_page_and_keeps_the_text(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->from(route('app.sashes.create', $project))
            ->followingRedirects()
            ->post(route('app.sashes.store-many', $project), ['lines' => "A-1\nA-1; Kök"])
            ->assertOk()
            ->assertSee('Rad 2: koden A-1 finns redan på rad 1.')
            ->assertSee("A-1\nA-1; Kök", false);
    }

    public function test_bulk_create_is_scoped_to_the_workshop_and_requires_login(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $otherProject = Project::factory()->create(['workshop_id' => $otherOwner->workshop_id]);

        $this->post(route('app.sashes.store-many', $otherProject), ['lines' => 'A-1'])
            ->assertRedirect(route('login'));

        $this->actingAs($owner)
            ->post(route('app.sashes.store-many', $otherProject), ['lines' => 'A-1'])
            ->assertNotFound();

        $this->assertDatabaseCount('window_sashes', 0);
    }
}
