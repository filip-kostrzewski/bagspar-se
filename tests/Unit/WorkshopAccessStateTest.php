<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkshopAccessStateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-17 12:00:00');
    }

    public function test_access_ends_at_the_latest_of_trial_payment_and_order_grace(): void
    {
        $workshop = Workshop::factory()->make([
            'trial_ends_at' => now()->addDays(5),
            'paid_until' => now()->addDays(40),
            'subscription_ordered_at' => now()->subDays(10),
        ]);
        $this->assertTrue(now()->addDays(40)->equalTo($workshop->accessEndsAt()));

        $workshop->paid_until = now()->addDays(2);
        $this->assertTrue(now()->addDays(20)->equalTo($workshop->accessEndsAt()));

        $workshop->subscription_ordered_at = now()->subDays(29);
        $this->assertTrue(now()->addDays(5)->equalTo($workshop->accessEndsAt()));

        $workshop->trial_ends_at = null;
        $workshop->paid_until = null;
        $workshop->subscription_ordered_at = now()->subDays(29);
        $this->assertTrue(now()->addDay()->equalTo($workshop->accessEndsAt()));
    }

    public function test_a_workshop_without_any_dates_never_had_access(): void
    {
        $workshop = Workshop::factory()->make(['trial_ends_at' => null]);

        $this->assertNull($workshop->accessEndsAt());
        $this->assertFalse($workshop->hasAccess());
        $this->assertSame(Workshop::ACCESS_LOCKED, $workshop->accessState());
        $this->assertSame(0, $workshop->accessDaysLeft());
    }

    public function test_a_workshop_in_its_trial_is_trialing(): void
    {
        $workshop = Workshop::factory()->make(['trial_ends_at' => now()->addDays(5)]);

        $this->assertSame(Workshop::ACCESS_TRIAL, $workshop->accessState());
        $this->assertTrue($workshop->hasAccess());
        $this->assertTrue($workshop->isTrialing());
        $this->assertFalse($workshop->isLocked());
        $this->assertFalse($workshop->hasOrderedSubscription());
        $this->assertFalse($workshop->hasPaidAccess());
        $this->assertSame(5, $workshop->accessDaysLeft());
    }

    public function test_an_ordered_workshop_waits_for_its_invoice_inside_the_grace_period(): void
    {
        $workshop = Workshop::factory()->make([
            'trial_ends_at' => now()->subDays(10),
            'subscription_ordered_at' => now()->subDays(5),
        ]);

        $this->assertSame(Workshop::ACCESS_ORDERED, $workshop->accessState());
        $this->assertTrue($workshop->hasOrderedSubscription());
        $this->assertSame(25, $workshop->accessDaysLeft());
    }

    public function test_a_workshop_with_a_future_paid_date_is_active(): void
    {
        $workshop = Workshop::factory()->make([
            'trial_ends_at' => now()->subDays(60),
            'subscription_ordered_at' => now()->subDays(50),
            'paid_until' => now()->addDays(12),
        ]);

        $this->assertSame(Workshop::ACCESS_ACTIVE, $workshop->accessState());
        $this->assertTrue($workshop->hasPaidAccess());
        $this->assertSame(12, $workshop->accessDaysLeft());
    }

    public function test_an_expired_payment_and_grace_lock_the_workshop(): void
    {
        $workshop = Workshop::factory()->make([
            'trial_ends_at' => now()->subDays(90),
            'subscription_ordered_at' => now()->subDays(60),
            'paid_until' => now()->subDay(),
        ]);

        $this->assertSame(Workshop::ACCESS_LOCKED, $workshop->accessState());
        $this->assertTrue($workshop->isLocked());
        $this->assertFalse($workshop->hasPaidAccess());
        $this->assertSame(0, $workshop->accessDaysLeft());
    }

    public function test_an_ended_trial_without_an_order_is_locked(): void
    {
        $workshop = Workshop::factory()->make(['trial_ends_at' => now()->subSecond()]);

        $this->assertSame(Workshop::ACCESS_LOCKED, $workshop->accessState());
        $this->assertSame(0, $workshop->accessDaysLeft());
    }

    public function test_the_order_grace_ends_thirty_days_after_the_order(): void
    {
        $workshop = Workshop::factory()->make([
            'trial_ends_at' => now()->subDays(60),
            'subscription_ordered_at' => now()->subDays(30)->addMinute(),
        ]);
        $this->assertTrue($workshop->hasAccess());

        $workshop->subscription_ordered_at = now()->subDays(30)->subMinute();
        $this->assertFalse($workshop->hasAccess());
    }

    public function test_the_plan_label_and_plan_flag(): void
    {
        $this->assertSame('Månadsabonnemang', Workshop::factory()->make(['subscription_plan' => 'monthly'])->planLabel());
        $this->assertSame('Årsabonnemang', Workshop::factory()->make(['subscription_plan' => 'yearly'])->planLabel());
        $this->assertNull(Workshop::factory()->make()->planLabel());
        $this->assertTrue(Workshop::factory()->make(['subscription_plan' => 'yearly'])->isOnYearlyPlan());
        $this->assertFalse(Workshop::factory()->make(['subscription_plan' => 'monthly'])->isOnYearlyPlan());
    }

    /**
     * @return array<string, array{int, int, int, int}>
     */
    public static function seatPrices(): array
    {
        return [
            'one user' => [1, 0, 1490, 14900],
            'three users' => [3, 0, 1490, 14900],
            'five users' => [5, 2, 2188, 14900 + 2 * 349 * 12],
        ];
    }

    #[DataProvider('seatPrices')]
    public function test_extra_users_are_priced_per_month_and_per_year(int $users, int $extraSeats, int $monthly, int $yearly): void
    {
        $workshop = Workshop::factory()->create();
        User::factory()->count($users)->create(['workshop_id' => $workshop->getKey()]);

        $this->assertSame($users, $workshop->seatCount());
        $this->assertSame($extraSeats, $workshop->extraSeatCount());
        $this->assertSame($monthly, $workshop->monthlyPriceKr());
        $this->assertSame($yearly, $workshop->yearlyPriceKr());
    }
}
