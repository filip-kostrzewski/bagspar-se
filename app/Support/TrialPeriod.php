<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * A new workshop gets a free trial that ends at the last second of its
 * final day in Sweden.
 *
 * Returned instants are UTC. Eloquent writes a datetime from the Carbon
 * wall clock, so a Stockholm instance would be stored one hour late.
 */
final class TrialPeriod
{
    public static function forAccountCreatedAt(Carbon $createdAt): Carbon
    {
        return $createdAt->copy()
            ->timezone('Europe/Stockholm')
            ->addDays(Pricing::trialDays())
            ->endOfDay()
            ->startOfSecond()
            ->utc();
    }
}
