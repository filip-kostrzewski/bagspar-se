<?php

namespace Tests\Unit\Support;

use App\Support\TrialPeriod;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TrialPeriodTest extends TestCase
{
    public function test_the_trial_ends_at_the_last_second_of_the_thirtieth_swedish_day(): void
    {
        $endsAt = TrialPeriod::forAccountCreatedAt(Carbon::parse('2026-10-17 15:00:00', 'Europe/Stockholm'));

        $this->assertSame('UTC', $endsAt->getTimezone()->getName());
        $this->assertSame(
            Carbon::parse('2026-11-16 23:59:59', 'Europe/Stockholm')->utc()->timestamp,
            $endsAt->timestamp,
        );
        $this->assertSame('2026-11-16 22:59:59', $endsAt->format('Y-m-d H:i:s'));
    }

    public function test_a_late_evening_signup_counts_the_swedish_date_not_the_utc_date(): void
    {
        $endsAt = TrialPeriod::forAccountCreatedAt(Carbon::parse('2026-10-17 23:30:00', 'Europe/Stockholm')->utc());

        $this->assertSame('2026-11-16 22:59:59', $endsAt->format('Y-m-d H:i:s'));
    }

    public function test_the_trial_length_follows_the_configuration(): void
    {
        config(['bagspar.trial_days' => 14]);

        $endsAt = TrialPeriod::forAccountCreatedAt(Carbon::parse('2026-10-17 15:00:00', 'Europe/Stockholm'));

        $this->assertSame('2026-10-31 22:59:59', $endsAt->format('Y-m-d H:i:s'));
    }

    public function test_the_given_instance_is_not_modified(): void
    {
        $createdAt = Carbon::parse('2026-10-17 15:00:00', 'Europe/Stockholm');

        TrialPeriod::forAccountCreatedAt($createdAt);

        $this->assertSame('2026-10-17 15:00:00', $createdAt->format('Y-m-d H:i:s'));
        $this->assertSame('Europe/Stockholm', $createdAt->getTimezone()->getName());
    }
}
