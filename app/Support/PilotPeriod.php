<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The free pilot ends at the last second of 31 December 2026 in Sweden.
 *
 * Returned instants are UTC. Eloquent writes a datetime from the Carbon
 * wall clock, so a Stockholm instance would be stored one hour late.
 */
final class PilotPeriod
{
    public static function endsAt(): Carbon
    {
        return Carbon::parse('2026-12-31 23:59:59', 'Europe/Stockholm')->utc();
    }

    public static function forAccountCreatedAt(Carbon $createdAt): Carbon
    {
        $endsAt = self::endsAt();

        if ($createdAt->greaterThan($endsAt)) {
            return $createdAt->copy()->utc();
        }

        return $endsAt->copy();
    }
}
