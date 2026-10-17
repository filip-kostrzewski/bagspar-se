<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_workshop_can_share_a_public_status_link_without_internal_notes(): void
    {
        Storage::fake('local');
        $this->travelTo(Carbon::parse('2026-10-15 22:30:00', 'UTC'));

        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $owner->workshop->update([
            'name' => 'Ekens Fönster',
            'organization_number' => '559214-9370',
            'contact_email' => 'verkstad@example.test',
            'address' => 'Storgatan 1, Uppsala',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'customer_name' => 'BRF Hemlig',
            'location' => 'Våning 2',
            'notes' => 'HEMLIG PROJEKTANTECKNING',
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'B-24',
            'room' => 'Kök',
            'stage' => 'glazing',
            'notes' => 'HEMLIG BÅGANTECKNING',
        ]);
        $hidden = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'moment' => null,
            'original_filename' => 'intern-fil.jpg',
        ]);
        Storage::disk('local')->put($hidden->path, 'older-photo');

        $this->actingAs($owner)->patch(route('app.settings.update'), [
            'name' => 'Ekens Fönster',
            'organization_number' => '559214-9370',
            'contact_email' => 'verkstad@example.test',
            'address' => 'Storgatan 1, Uppsala',
            'logo' => UploadedFile::fake()->image('logo.png', 64, 64),
        ])->assertRedirect();

        $this->actingAs($member)->post(route('app.sashes.photos.store', [$project, $sash]), [
            'photo' => UploadedFile::fake()->image('fore.jpg', 80, 60),
            'moment' => 'before',
        ])->assertRedirect();
        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'photo' => UploadedFile::fake()->image('efter.jpg', 80, 60),
            'moment' => 'after',
        ])->assertRedirect();

        $this->post(route('app.projects.client-status.update', $project), [
            'action' => 'enable',
        ])->assertRedirect()->assertSessionHas('status', 'Kundens statuslänk är på.');

        $project->refresh();
        $this->assertTrue($project->clientStatusIsPublic());
        $token = $project->client_status_token;
        $this->assertIsString($token);

        $before = SashPhoto::query()->where('moment', 'before')->firstOrFail();
        $after = SashPhoto::query()->where('moment', 'after')->firstOrFail();
        $status = $this->get(route('client-status.show', ['token' => $token]));

        $status->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertSee('Ekens Fönster', false)
            ->assertSee('Org.nr 559214-9370', false)
            ->assertSee('verkstad@example.test', false)
            ->assertSee('Storgatan 1, Uppsala', false)
            ->assertSee(route('client-status.logo', ['token' => $token]), false)
            ->assertSee('Villa Ek', false)
            ->assertSee('Våning 2', false)
            ->assertSee('B-24', false)
            ->assertSee('Kök', false)
            ->assertSee('Glasning', false)
            ->assertSee('Före · 2026-10-16', false)
            ->assertSee('Efter · 2026-10-16', false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('data-enlarge', false)
            ->assertSeeInOrder([
                route('client-status.photos', ['token' => $token, 'photo' => $hidden]),
                route('client-status.photos', ['token' => $token, 'photo' => $before]),
                route('client-status.photos', ['token' => $token, 'photo' => $after]),
            ], false)
            ->assertSee('BRF Hemlig', false)
            ->assertDontSee('HEMLIG PROJEKTANTECKNING', false)
            ->assertDontSee('HEMLIG BÅGANTECKNING', false)
            ->assertDontSee('intern-fil.jpg', false)
            ->assertDontSee('Pågår', false);

        $this->get(route('client-status.photos', ['token' => $token, 'photo' => $before]))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get(route('client-status.photos', ['token' => $token, 'photo' => $hidden]))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get(route('client-status.logo', ['token' => $token]))->assertOk();

        $report = $this->actingAs($member)->get(route('app.projects.report', $project));
        $report->assertOk()
            ->assertSee('Status för kunden', false)
            ->assertSee(route('client-status.show', ['token' => $token]), false)
            ->assertSee('<svg', false);

        $label = $this->get(route('app.sashes.label', [$project, $sash]));
        $label->assertOk()
            ->assertSee(route('app.sashes.qr', [$project, $sash]), false)
            ->assertDontSee('/status/'.$token, false);

        $this->post(route('app.projects.client-status.update', $project), ['action' => 'disable'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Kundens statuslänk är av.');

        $this->get(route('client-status.show', ['token' => $token]))->assertNotFound();
        $this->get(route('client-status.photos', ['token' => $token, 'photo' => $before]))->assertNotFound();

        $this->actingAs($member)->post(route('app.projects.client-status.update', $project), ['action' => 'enable'])
            ->assertRedirect();
        $project->refresh();
        $this->assertSame($token, $project->client_status_token);

        $this->post(route('app.projects.client-status.update', $project), ['action' => 'rotate'])
            ->assertSessionHas('status', 'En ny statuslänk har skapats. Den gamla länken fungerar inte längre.');
        $project->refresh();
        $this->assertNotSame($token, $project->client_status_token);
        $this->get(route('client-status.show', ['token' => $token]))->assertNotFound();
        $this->get(route('client-status.show', ['token' => $project->client_status_token]))->assertOk();
    }

    public function test_status_links_are_not_guessable_and_stay_inside_the_project(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $otherProject = Project::factory()->create(['workshop_id' => $other->workshop_id]);
        $otherSash = WindowSash::factory()->create(['project_id' => $otherProject->getKey()]);

        $this->actingAs($other)->post(route('app.sashes.photos.store', [$otherProject, $otherSash]), [
            'photo' => UploadedFile::fake()->image('annan.jpg', 40, 40),
            'moment' => 'before',
        ])->assertRedirect();
        $otherPhoto = SashPhoto::query()->firstOrFail();

        $this->actingAs($owner)->post(route('app.projects.client-status.update', $project), ['action' => 'enable']);
        $token = $project->fresh()?->client_status_token;
        $this->assertIsString($token);

        $this->get(route('client-status.show', ['token' => str_repeat('a', 48)]))->assertNotFound();
        $this->get('/status/kort')->assertNotFound();
        $this->get(route('client-status.photos', ['token' => $token, 'photo' => $otherPhoto]))->assertNotFound();

        $this->actingAs($other)
            ->post(route('app.projects.client-status.update', $project), ['action' => 'disable'])
            ->assertNotFound();

        $this->post(route('auth.logout'));
        $this->post(route('app.projects.client-status.update', $project), ['action' => 'disable'])
            ->assertRedirect(route('login'));
    }
}
