<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_address_gives_a_swedish_404_for_guests_and_members(): void
    {
        $this->get('/finns-inte-alls')
            ->assertNotFound()
            ->assertSee('Sidan hittades inte', false)
            ->assertSee(route('home'), false);

        $this->actingAs(User::factory()->create())
            ->get('/finns-inte-alls')
            ->assertNotFound()
            ->assertSee('Sidan hittades inte', false)
            ->assertSee(route('app.dashboard'), false);
    }

    public function test_a_member_deleting_a_project_gets_a_swedish_403(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($member)
            ->delete(route('app.projects.destroy', $project))
            ->assertForbidden()
            ->assertSee('Du saknar behörighet', false)
            ->assertSee(route('app.dashboard'), false);

        $this->assertModelExists($project);
    }

    public function test_a_wrong_method_gives_a_swedish_405_and_a_typed_logout_address_redirects(): void
    {
        $this->get('/logga-ut')->assertRedirect('/logga-in');

        $this->get('/intresse')
            ->assertStatus(405)
            ->assertSee('Den här adressen tar inte emot det', false);
    }

    public function test_a_post_without_a_csrf_token_gives_a_swedish_419(): void
    {
        Route::post('/csrf-probe', fn (): string => 'ok')->middleware('web');
        $this->app['env'] = 'local';

        $this->post('/csrf-probe')
            ->assertStatus(419)
            ->assertSee('Sessionen har gått ut', false);
    }

    public function test_the_payment_required_page_links_owners_to_the_subscription(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner);
        $html = view('errors.402')->render();

        $this->assertStringContainsString('Abonnemang krävs', $html);
        $this->assertStringContainsString(route('app.billing.edit'), $html);

        $this->app['auth']->logout();
        $guest = view('errors.402')->render();

        $this->assertStringContainsString('Abonnemang krävs', $guest);
        $this->assertStringNotContainsString(route('app.billing.edit'), $guest);
        $this->assertStringContainsString(route('login'), $guest);
    }

    public function test_the_500_page_blames_us_and_shows_the_contact_address(): void
    {
        $html = view('errors.500')->render();

        $this->assertStringContainsString('Något gick fel hos oss', $html);
        $this->assertStringContainsString(config('bagspar.contact_email'), $html);
        $this->assertStringContainsString('href="/"', $html);
    }

    public function test_the_503_page_is_self_contained(): void
    {
        $html = view('errors.503')->render();

        $this->assertStringContainsString('Bågspår uppdateras just nu', $html);
        $this->assertStringContainsString('href="/"', $html);
        $this->assertStringNotContainsString(url('/app'), $html);
        $this->assertStringNotContainsString(route('login'), $html);
        $this->assertStringNotContainsString('workshop.css', $html);
        $this->assertStringNotContainsString('<form', $html);
    }

    public function test_the_500_and_503_pages_render_for_a_logged_in_user_too(): void
    {
        $this->actingAs(User::factory()->create());

        $this->assertStringContainsString('Något gick fel hos oss', view('errors.500')->render());
        $this->assertStringContainsString('Bågspår uppdateras just nu', view('errors.503')->render());
    }
}
