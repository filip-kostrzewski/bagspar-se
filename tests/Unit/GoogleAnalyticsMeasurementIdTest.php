<?php

namespace Tests\Unit;

use App\Support\GoogleAnalyticsMeasurementId;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class GoogleAnalyticsMeasurementIdTest extends TestCase
{
    #[TestWith(['G-TEST1234', 'G-TEST1234'])]
    #[TestWith([' g-ab12cd ', 'G-AB12CD'])]
    #[TestWith(['G-ABCD', 'G-ABCD'])]
    public function test_normalize_accepts_a_ga4_measurement_id(string $value, string $expected): void
    {
        $this->assertSame($expected, GoogleAnalyticsMeasurementId::normalize($value));
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['   '])]
    #[TestWith(['UA-12345678'])]
    #[TestWith(['G-ABC'])]
    #[TestWith(['G-TEST1234<script>'])]
    #[TestWith([123])]
    public function test_normalize_rejects_a_missing_or_invalid_measurement_id(mixed $value): void
    {
        $this->assertNull(GoogleAnalyticsMeasurementId::normalize($value));
    }
}
