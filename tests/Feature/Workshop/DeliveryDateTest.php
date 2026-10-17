<?php

namespace Tests\Feature\Workshop;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeliveryDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_project_can_store_and_show_a_delivery_date(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
            'phone' => '070-999 88 77',
        ]);

        $this->actingAs($owner)
            ->post(route('app.projects.store'), [
                'name' => 'Villa Ek',
                'delivery_date' => '2026-10-20',
            ])
            ->assertRedirect();

        $project = Project::query()->where('name', 'Villa Ek')->firstOrFail();
        $this->assertSame('2026-10-20', $project->delivery_date?->toDateString());

        $this->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Beräknad leverans', false)
            ->assertSee('20 okt 2026', false)
            ->assertSee('2026-10-20', false);

        $this->get(route('app.projects.report', $project))
            ->assertOk()
            ->assertSee('Beräknad leverans', false)
            ->assertSee('20 okt 2026', false);

        $this->patch(route('app.projects.update', $project), [
            'form_context' => 'project',
            'name' => 'Villa Ek',
            'delivery_date' => '2026-11-02',
        ])->assertRedirect();
        $this->assertSame('2026-11-02', $project->refresh()->delivery_date?->toDateString());

        $this->from(route('app.projects.show', $project))
            ->patch(route('app.projects.update', $project), [
                'form_context' => 'project',
                'name' => 'Villa Ek',
                'delivery_date' => 'inte-ett-datum',
            ])
            ->assertRedirect(route('app.projects.show', $project))
            ->assertInvalid(['delivery_date' => 'Ange leveransdatumet som ett datum.']);
        $this->assertSame('2026-11-02', $project->refresh()->delivery_date?->toDateString());

        $this->patch(route('app.projects.update', $project), [
            'form_context' => 'status',
            'status' => 'completed',
        ])->assertRedirect();
        $project->refresh();
        $this->assertSame('completed', $project->status);
        $this->assertSame('2026-11-02', $project->delivery_date?->toDateString());

        $this->patch(route('app.projects.update', $project), [
            'form_context' => 'project',
            'name' => 'Villa Ek',
            'delivery_date' => '',
        ])->assertRedirect();
        $this->assertNull($project->refresh()->delivery_date);

        $project->forceFill([
            'customer_id' => $customer->getKey(),
            'customer_name' => $customer->name,
            'delivery_date' => '2026-10-20',
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('b', 48),
        ])->save();

        $this->get(route('client-status.show', ['token' => $project->client_status_token]))
            ->assertOk()
            ->assertSee('Beräknad leverans 20 okt 2026', false)
            ->assertDontSee('070-999 88 77', false)
            ->assertDontSee('Telefon', false);

        $project->forceFill(['delivery_date' => null])->save();
        $this->get(route('client-status.show', ['token' => $project->client_status_token]))
            ->assertOk()
            ->assertDontSee('Beräknad leverans', false);

        $project->forceFill(['delivery_date' => '2026-10-20'])->save();
        $export = $this->get(route('app.customers.export.json', $customer));
        $export->assertOk();
        $export->assertJsonPath('projekt.0.leveransdatum', '2026-10-20');
        $csv = $this->get(route('app.customers.export.csv', $customer))->streamedContent();
        $this->assertStringContainsString('Beräknad leverans', $csv);
        $this->assertStringContainsString('20 okt 2026', $csv);
        $this->assertStringNotContainsString('2026-10-20', $csv);
    }

    public function test_the_dashboard_groups_overdue_and_this_weeks_deliveries(): void
    {
        $this->travelTo(Carbon::parse('2026-10-17 10:00:00', 'Europe/Stockholm'));

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $overdue = $this->project($owner, 'Villa Sen', '2026-10-10', 'received');
        $lateThisWeek = $this->project($owner, 'Villa Tisdag', '2026-10-13', 'scraping');
        $today = $this->project($owner, 'Villa Idag', '2026-10-17', 'received');
        $sunday = $this->project($owner, 'Villa Söndag', '2026-10-18', 'received');
        $edge = $this->project($owner, 'Villa Sex', '2026-10-23', 'received');
        $later = $this->project($owner, 'Villa Sju', '2026-10-24', 'received');
        $ready = $this->project($owner, 'Villa Klar', '2026-10-01', 'ready');
        $empty = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Tom',
            'delivery_date' => '2026-10-01',
        ]);
        $archived = $this->project($owner, 'Villa Arkiv', '2026-10-01', 'received');
        $archived->forceFill(['archived_at' => '2026-10-01 00:00:00'])->save();
        $undated = $this->project($owner, 'Villa Utan', null, 'received');
        $this->project($other, 'Villa Främmande', '2026-10-01', 'received');

        $dashboard = $this->actingAs($owner)->get(route('app.dashboard'));
        $dashboard->assertOk();
        $html = $dashboard->getContent();
        $this->assertIsString($html);

        $overdueSection = $this->between($html, 'id="overdue-title"', 'id="upcoming-title"');
        $upcomingSection = $this->between($html, 'id="upcoming-title"', 'id="projects-title"');
        $this->assertStringContainsString('Försenade', $overdueSection);
        $this->assertStringContainsString('Villa Sen', $overdueSection);
        $this->assertStringContainsString('Villa Tisdag', $overdueSection);
        $this->assertStringContainsString('Villa Tom', $overdueSection);
        $this->assertStringNotContainsString('Villa Idag', $overdueSection);
        $this->assertStringNotContainsString('Villa Söndag', $overdueSection);
        $this->assertStringNotContainsString('Villa Klar', $overdueSection);
        $this->assertStringNotContainsString('Villa Arkiv', $overdueSection);
        $this->assertStringNotContainsString('Villa Utan', $overdueSection);
        $this->assertStringNotContainsString('Villa Främmande', $overdueSection);
        $this->assertStringContainsString('Kommande 7 dagar', $upcomingSection);
        $this->assertStringContainsString('Villa Idag', $upcomingSection);
        $this->assertStringContainsString('Villa Söndag', $upcomingSection);
        $this->assertStringContainsString('Villa Sex', $upcomingSection);
        $this->assertStringNotContainsString('Villa Tisdag', $upcomingSection);
        $this->assertStringNotContainsString('Villa Sju', $upcomingSection);
        $this->assertStringNotContainsString('Villa Sen', $upcomingSection);
        $this->assertStringNotContainsString('Villa Klar', $upcomingSection);
        $this->assertStringNotContainsString('Villa Tom', $upcomingSection);
        $this->assertStringContainsString('Beräknad leverans 18 okt 2026', $html);
        $this->assertNotNull($lateThisWeek->getKey());
        $this->assertNotNull($today->getKey());
        $this->assertNotNull($edge->getKey());
        $this->assertNotNull($later->getKey());
        $this->assertNotNull($empty->getKey());
        $this->assertNotNull($ready->getKey());
        $this->assertNotNull($undated->getKey());

        $filtered = $this->get(route('app.dashboard', ['leverans' => 'forsenade']));
        $filtered->assertOk()->assertSee('value="forsenade" selected', false);
        $list = $this->after($filtered->getContent(), 'id="projects-title"');
        $this->assertStringContainsString('Villa Sen', $list);
        $this->assertStringContainsString('Villa Tisdag', $list);
        $this->assertStringContainsString('Villa Tom', $list);
        $this->assertStringNotContainsString('Villa Söndag', $list);
        $this->assertStringNotContainsString('Villa Klar', $list);
        $this->assertStringNotContainsString('Villa Arkiv', $list);
        $this->assertStringNotContainsString('Villa Utan', $list);
        $this->assertStringNotContainsString('Villa Främmande', $list);

        $kommande = $this->get(route('app.dashboard', ['leverans' => 'kommande']))->assertOk();
        $kommandeHtml = $kommande->getContent();
        $this->assertIsString($kommandeHtml);
        $this->assertStringNotContainsString('id="overdue-title"', $kommandeHtml);
        $this->assertStringNotContainsString('id="upcoming-title"', $kommandeHtml);
        $this->assertSame(1, substr_count($kommandeHtml, 'Villa Idag'));
        $week = $this->after($kommandeHtml, 'id="projects-title"');
        $this->assertStringContainsString('Villa Idag', $week);
        $this->assertStringContainsString('Villa Söndag', $week);
        $this->assertStringContainsString('Villa Sex', $week);
        $this->assertStringNotContainsString('Villa Tisdag', $week);
        $this->assertStringNotContainsString('Villa Sju', $week);
        $this->assertStringNotContainsString('Villa Sen', $week);
        $this->assertStringNotContainsString('Villa Tom', $week);

        $oldWeek = $this->after($this->get(route('app.dashboard', ['leverans' => 'denna-vecka']))->assertOk()->getContent(), 'id="projects-title"');
        $this->assertStringContainsString('Villa Sen', $oldWeek);
        $this->assertStringContainsString('Villa Sju', $oldWeek);

        $archive = $this->get(route('app.dashboard', ['arkiv' => 1, 'leverans' => 'forsenade']));
        $archive->assertOk();
        $archiveHtml = $archive->getContent();
        $this->assertIsString($archiveHtml);
        $this->assertStringNotContainsString('id="overdue-title"', $archiveHtml);
        $this->assertStringNotContainsString('id="upcoming-title"', $archiveHtml);
        $this->assertStringContainsString('Villa Arkiv', $archiveHtml);
        $this->assertStringNotContainsString('Villa Sen', $archiveHtml);

        $ignored = $this->after($this->get(route('app.dashboard', ['leverans' => 'okänd']))->assertOk()->getContent(), 'id="projects-title"');
        $this->assertStringContainsString('Villa Sen', $ignored);
        $this->assertStringContainsString('Villa Klar', $ignored);
        $this->assertStringContainsString('Villa Utan', $ignored);
        $this->assertStringNotContainsString('Villa Arkiv', $ignored);
        $this->assertStringNotContainsString('Villa Främmande', $ignored);
    }

    private function project(User $user, string $name, ?string $date, string $stage): Project
    {
        $project = Project::factory()->create([
            'workshop_id' => $user->workshop_id,
            'name' => $name,
            'delivery_date' => $date,
        ]);
        WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'stage' => $stage,
        ]);

        return $project;
    }

    private function between(string $html, string $start, string $end): string
    {
        $from = strpos($html, $start);
        $to = $from === false ? false : strpos($html, $end, $from + strlen($start));
        $this->assertNotFalse($from);
        $this->assertNotFalse($to);

        return substr($html, (int) $from, (int) $to - (int) $from);
    }

    private function after(string|false $html, string $start): string
    {
        $this->assertIsString($html);
        $from = strpos($html, $start);
        $this->assertNotFalse($from);

        return substr($html, $from);
    }
}
