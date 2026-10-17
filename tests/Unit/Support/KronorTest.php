<?php

namespace Tests\Unit\Support;

use App\Support\Kronor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class KronorTest extends TestCase
{
    public function test_optional_amounts_accept_a_comma_or_a_dot(): void
    {
        $this->assertNull(Kronor::parseOptional(null));
        $this->assertNull(Kronor::parseOptional(''));
        $this->assertNull(Kronor::parseOptional('  '));
        $this->assertSame(65000, Kronor::parseOptional('650'));
        $this->assertSame(65050, Kronor::parseOptional('650,50'));
        $this->assertSame(65050, Kronor::parseOptional('650.50'));
        $this->assertSame(100000, Kronor::parseOptional('1 000'));
        $this->assertSame(100_000_000, Kronor::parseOptional('1000000'));
        $this->assertSame('975 kr', Kronor::format(97500));
        $this->assertSame('1 600', Kronor::amount(160000));
    }

    public function test_an_amount_cannot_mix_comma_and_dot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Beloppet kan inte innehålla både komma och punkt.');

        Kronor::parseOptional('650,50.1');
    }

    public function test_an_amount_above_one_million_kronor_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Beloppet får vara högst 1 000 000 kr.');

        Kronor::parseOptional('1000001');
    }

    public function test_an_amount_with_more_than_two_decimals_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Beloppet måste vara kronor med högst två decimaler.');

        Kronor::parseOptional('650,505');
    }
}
