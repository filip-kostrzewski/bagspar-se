<?php

namespace Tests\Unit\Support;

use App\Support\SwedishPhone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SwedishPhoneTest extends TestCase
{
    public function test_national_and_international_numbers_are_stored_as_swedish_e164(): void
    {
        $this->assertSame('+46701234567', SwedishPhone::parseOptional('070-123 45 67'));
        $this->assertSame('+46701234567', SwedishPhone::parseOptional('+46 70 123 45 67'));
        $this->assertSame('+46701234567', SwedishPhone::parseOptional('+46 (0)70 123 45 67'));
        $this->assertSame('+46701234567', SwedishPhone::parseOptional('0046 70 123 45 67'));
        $this->assertSame('+46701234567', SwedishPhone::parseOptional('070.123.45.67'));
        $this->assertSame('+4681234567', SwedishPhone::parseOptional('08-123 45 67'));
        $this->assertNull(SwedishPhone::parseOptional(null));
        $this->assertNull(SwedishPhone::parseOptional('   '));
        $this->assertNull(SwedishPhone::parseOptional('---'));
    }

    public function test_numbers_outside_sweden_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SwedishPhone::parseOptional('+47 70 123 45 67');
    }

    public function test_a_bare_country_code_without_a_plus_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SwedishPhone::parseOptional('46701234567');
    }

    public function test_letters_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SwedishPhone::parseOptional('070-ABC 45 67');
    }

    public function test_display_format_groups_the_subscriber_after_the_area_code(): void
    {
        $this->assertSame('070-123 45 67', SwedishPhone::format('+46701234567'));
        $this->assertSame('08-123 45 67', SwedishPhone::format('+4681234567'));
        $this->assertSame('031-123 45 67', SwedishPhone::format('+46311234567'));
        $this->assertSame('0500-12 34 56', SwedishPhone::format('+46500123456'));
        $this->assertSame('tel:+46701234567', SwedishPhone::telHref('+46701234567'));
    }
}
