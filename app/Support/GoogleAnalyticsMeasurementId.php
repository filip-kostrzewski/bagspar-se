<?php

namespace App\Support;

class GoogleAnalyticsMeasurementId
{
    /**
     * Accept a GA4 measurement ID. Anything else disables the tag.
     */
    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $measurementId = strtoupper(trim($value));

        if (preg_match('/^G-[A-Z0-9]{4,}$/', $measurementId) !== 1) {
            return null;
        }

        return $measurementId;
    }
}
