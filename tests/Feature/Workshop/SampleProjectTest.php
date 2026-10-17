<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashEvent;
use App\Models\SashMaterial;
use App\Models\SashTimeEntry;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_owner_creates_the_sample_project_with_realistic_data(): void
    {
        $owner = User::factory()->create(['name' => 'Ada Ek']);

        $response = $this->actingAs($owner)->post(route('app.sample-project.store'));

        $project = Project::query()->sole();
        $response->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHas('status', 'Exempelprojektet är skapat. Ta bort det när du vill.');

        $this->assertSame($owner->workshop_id, $project->workshop_id);
        $this->assertSame('Exempelprojekt – Villa Björk', $project->name);
        $this->assertSame('Björkvägen 4, 756 52 Uppsala', $project->location);
        $this->assertSame('Det här är ett exempel som visar hur ett projekt ser ut. Ta bort det när du vill.', $project->notes);
        $this->assertSame(now()->addWeeks(3)->toDateString(), $project->delivery_date->toDateString());
        $this->assertTrue($project->clientStatusIsPublic());
        $this->assertSame(48, strlen($project->client_status_token));

        $customer = $project->customer;
        $this->assertSame(1, $owner->workshop->customers()->count());
        $this->assertSame('Exempelkund: Familjen Björk', $customer->name);
        $this->assertSame('Anna Björk', $customer->contact_name);
        $this->assertSame('Björkvägen 4, 756 52 Uppsala', $customer->address);
        $this->assertNull($customer->email);
        $this->assertNull($customer->phone);

        $stages = $project->windowSashes()->orderBy('code')->pluck('stage', 'code')->all();
        $this->assertSame([
            '2-KÖK-F1-B1' => 'ready',
            '2-KÖK-F1-B2' => 'glazing',
            '2-SOV-F1-B1' => 'scraping',
            '2-SOV-F1-B2' => 'received',
            '2-VRD-F1-B1' => 'painting',
            '2-VRD-F2-B1' => 'repair',
        ], $stages);
        $this->assertSame(['Kök', 'Sovrum', 'Vardagsrum'], $project->windowSashes()->distinct()->orderBy('room')->pluck('room')->all());
        $this->assertSame(6, WindowSash::query()->whereNotNull('notes')->whereNotNull('location')->count());

        $this->assertSame(3, SashTimeEntry::query()->count());
        $this->assertSame(270, (int) SashTimeEntry::query()->sum('minutes'));
        $this->assertSame(
            ['Skrapning', 'Skrapning', 'Trälagning'],
            SashTimeEntry::query()->orderBy('note')->pluck('note')->all(),
        );
        $this->assertSame(3, SashMaterial::query()->count());
        $kitt = SashMaterial::query()->where('description', 'Linoljekitt')->sole();
        $this->assertSame(16500, $kitt->unit_price_ore);
        $this->assertSame('1', SashMaterial::quantityLabel($kitt->quantity));
        $glas = SashMaterial::query()->where('description', 'Fönsterglas 3 mm')->sole();
        $this->assertSame(32000, $glas->unit_price_ore);
        $this->assertSame('2', SashMaterial::quantityLabel($glas->quantity));

        $ready = $project->windowSashes()->where('code', '2-KÖK-F1-B1')->sole();
        $this->assertSame(
            ['received', 'scraping', 'repair', 'glazing', 'painting', 'ready'],
            $ready->events()->where('kind', SashEvent::KIND_STAGE)->orderBy('id')->pluck('stage')->all(),
        );
        $this->assertSame(['Ada Ek'], $ready->events()->distinct()->pluck('user_name')->all());
        $this->assertSame(2, $ready->events()->where('kind', SashEvent::KIND_TIME)->count());
        $this->assertSame(2, $ready->events()->where('kind', SashEvent::KIND_MATERIAL)->count());
        $this->assertSame(0, $project->windowSashes()->withCount('photos')->get()->sum('photos_count'));
    }

    public function test_the_sample_project_pages_render(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('app.sample-project.store'));
        $project = Project::query()->sole();
        $sash = $project->windowSashes()->where('code', '2-KÖK-F1-B1')->sole();

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Exempelprojekt – Villa Björk')
            ->assertSee('Det här är ett exempel som visar hur ett projekt ser ut. Ta bort det när du vill.')
            ->assertSee('2-VRD-F2-B1');

        $this->actingAs($owner)
            ->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Två rutor spruckna')
            ->assertSee('Linoljekitt')
            ->assertSee('Fönsterglas 3 mm');

        $this->get(route('client-status.show', $project->client_status_token))
            ->assertOk()
            ->assertSee('Exempelprojekt – Villa Björk');
    }

    public function test_a_second_call_redirects_to_the_existing_sample_project(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('app.sample-project.store'));
        $project = Project::query()->sole();

        $this->actingAs($owner)
            ->post(route('app.sample-project.store'))
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHas('status', 'Exempelprojektet finns redan.');

        $this->assertSame(1, Project::query()->count());
        $this->assertSame(6, WindowSash::query()->count());
        $this->assertSame(1, $owner->workshop->customers()->count());
    }

    public function test_a_member_can_also_create_the_sample_project(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Bo Berg']);

        $this->actingAs($member)
            ->post(route('app.sample-project.store'))
            ->assertRedirect(route('app.projects.show', Project::query()->sole()));

        $this->assertSame($owner->workshop_id, Project::query()->sole()->workshop_id);
        $this->assertSame('Bo Berg', SashTimeEntry::query()->first()->user_name);
    }

    public function test_the_sample_project_does_not_reuse_another_workshops_example(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other)->post(route('app.sample-project.store'));
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('app.sample-project.store'))
            ->assertSessionHas('status', 'Exempelprojektet är skapat. Ta bort det när du vill.');

        $this->assertSame(2, Project::query()->count());
        $this->assertSame(1, $owner->workshop->projects()->count());
    }

    public function test_guests_cannot_create_the_sample_project(): void
    {
        $this->post(route('app.sample-project.store'))->assertRedirect();

        $this->assertSame(0, Project::query()->count());
    }

    public function test_the_empty_dashboard_offers_the_sample_project(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk()
            ->assertSee('Skapa första projektet')
            ->assertSee('Visa ett exempelprojekt')
            ->assertSee('Sex bågar med steg, anteckningar, tid och material. Går att ta bort.')
            ->assertSee(route('app.sample-project.store'), false);

        Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk()
            ->assertDontSee('Visa ett exempelprojekt');
    }

    public function test_the_sample_project_shows_up_in_the_rot_underlag(): void
    {
        $owner = User::factory()->create();
        $owner->workshop->update(['hourly_rate_ore' => 60000]);
        $this->actingAs($owner)->post(route('app.sample-project.store'));
        $project = Project::query()->sole();

        $this->actingAs($owner)
            ->get(route('app.projects.rot', $project))
            ->assertOk()
            ->assertSee('Linoljekitt')
            ->assertSee('Trälagning')
            ->assertSee('2 700 kr')
            ->assertSee('3 625 kr');
    }

    public function test_the_sample_project_can_be_deleted_with_the_normal_flow(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('app.sample-project.store'));
        $project = Project::query()->sole();

        $this->actingAs($owner)
            ->delete(route('app.projects.destroy', $project), ['confirmation' => 'ta-bort'])
            ->assertRedirect(route('app.dashboard'));

        $this->assertSame(0, Project::query()->count());
        $this->assertSame(0, WindowSash::query()->count());
    }
}
