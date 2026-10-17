<?php

namespace Tests\Feature\Workshop;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-17 12:00:00');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function ownerOfWorkshop(array $attributes = []): User
    {
        return User::factory()->create([
            'workshop_id' => Workshop::factory()->create($attributes)->getKey(),
        ]);
    }

    private function ownerWithEndedTrial(): User
    {
        return $this->ownerOfWorkshop(['trial_ends_at' => now()->subDay()]);
    }

    public function test_a_trialing_owner_reaches_the_dashboard(): void
    {
        $owner = $this->ownerOfWorkshop(['trial_ends_at' => now()->addDays(10)]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk();
    }

    public function test_a_locked_workshop_is_sent_to_the_billing_page(): void
    {
        $owner = $this->ownerWithEndedTrial();

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertRedirect(route('app.billing.edit'));

        $this->actingAs($owner)
            ->get(route('app.projects.create'))
            ->assertRedirect(route('app.billing.edit'));
    }

    public function test_a_locked_workshop_can_still_download_its_export(): void
    {
        $owner = $this->ownerWithEndedTrial();

        $this->actingAs($owner)
            ->get(route('app.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_a_locked_workshop_can_open_the_billing_page_and_sees_why(): void
    {
        $owner = $this->ownerWithEndedTrial();

        $this->actingAs($owner)
            ->get(route('app.billing.edit'))
            ->assertOk()
            ->assertSee('Kontot är låst', false)
            ->assertSee('Beställ abonnemang', false);
    }

    public function test_a_json_request_to_a_locked_workshop_gets_payment_required(): void
    {
        $owner = $this->ownerWithEndedTrial();

        $this->actingAs($owner)
            ->getJson(route('app.dashboard'))
            ->assertStatus(402);
    }

    public function test_a_paid_workshop_keeps_access_after_the_trial(): void
    {
        $owner = $this->ownerOfWorkshop([
            'trial_ends_at' => now()->subDays(20),
            'subscription_plan' => Workshop::PLAN_MONTHLY,
            'subscription_ordered_at' => now()->subDays(60),
            'paid_until' => now()->addDays(10),
        ]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk();
    }

    public function test_an_ordered_workshop_keeps_access_during_the_grace_period(): void
    {
        $owner = $this->ownerOfWorkshop([
            'trial_ends_at' => now()->subDays(20),
            'subscription_plan' => Workshop::PLAN_MONTHLY,
            'subscription_ordered_at' => now()->subDays(10),
        ]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk();
    }

    public function test_an_ordered_workshop_is_locked_once_the_grace_period_has_passed(): void
    {
        $owner = $this->ownerOfWorkshop([
            'trial_ends_at' => now()->subDays(40),
            'subscription_plan' => Workshop::PLAN_MONTHLY,
            'subscription_ordered_at' => now()->subDays(31),
        ]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertRedirect(route('app.billing.edit'));
    }

    public function test_the_layout_warns_when_the_trial_ends_within_a_week(): void
    {
        $owner = $this->ownerOfWorkshop(['trial_ends_at' => now()->addDays(3)]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk()
            ->assertSee('workshop-access-notice', false)
            ->assertSee('Provperioden tar slut om 3 dagar', false);
    }

    public function test_the_layout_stays_quiet_while_the_trial_has_more_than_a_week_left(): void
    {
        $owner = $this->ownerOfWorkshop(['trial_ends_at' => now()->addDays(10)]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk()
            ->assertDontSee('Provperioden tar slut', false)
            ->assertDontSee('workshop-access-notice', false);
    }

    public function test_the_dashboard_shows_the_owner_how_many_trial_days_remain(): void
    {
        $owner = $this->ownerOfWorkshop(['trial_ends_at' => now()->addDays(10)->endOfDay()]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk()
            ->assertSee('Provperiod: 10 dagar kvar', false)
            ->assertSee('Beställ abonnemang', false)
            ->assertSee('590 kr/mån exkl. moms', false);
    }

    public function test_the_dashboard_trial_note_is_not_shown_to_members_or_paid_workshops(): void
    {
        $owner = $this->ownerOfWorkshop(['trial_ends_at' => now()->addDays(10)->endOfDay()]);
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($member)
            ->get(route('app.dashboard'))
            ->assertOk()
            ->assertDontSee('Provperiod:', false);

        $owner->workshop->update(['paid_until' => now()->addMonth()]);

        $this->actingAs($owner)
            ->get(route('app.dashboard'))
            ->assertOk()
            ->assertDontSee('Provperiod:', false);
    }

    public function test_a_member_of_a_locked_workshop_is_told_that_only_the_owner_can_order(): void
    {
        $owner = $this->ownerWithEndedTrial();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($member)
            ->get(route('app.billing.edit'))
            ->assertOk()
            ->assertSee('Bara ägaren kan beställa', false)
            ->assertDontSee('name="plan"', false);
    }
}
