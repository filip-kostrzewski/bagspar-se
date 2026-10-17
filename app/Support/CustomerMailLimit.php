<?php

namespace App\Support;

use App\Models\Workshop;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Daily cap on mail a workshop sends to its customers.
 *
 * Any account can type any address into a customer, so without a cap a
 * fresh account could use the service as a relay.
 */
final class CustomerMailLimit
{
    private const DECAY_SECONDS = 86400;

    public const MESSAGE = 'Verkstaden har nått dagens gräns för e-post till kunder. Försök igen i morgon.';

    /**
     * Record one send and tell whether it is still within today's cap.
     */
    public static function allow(Workshop $workshop): bool
    {
        $key = self::key($workshop);

        if (RateLimiter::tooManyAttempts($key, self::dailyLimit())) {
            return false;
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return true;
    }

    public static function dailyLimit(): int
    {
        return max(1, (int) config('bagspar.customer_mail_daily_limit', 50));
    }

    private static function key(Workshop $workshop): string
    {
        return 'customer-mail:'.$workshop->getKey();
    }
}
