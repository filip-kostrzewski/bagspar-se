<?php

namespace Tests\Unit\Support;

use App\Support\RotUnderlag;
use PHPUnit\Framework\TestCase;

class RotUnderlagTest extends TestCase
{
    public function test_labor_and_material_amounts_are_rounded_to_ore_without_a_deduction(): void
    {
        $this->assertSame(97500, RotUnderlag::laborOre(90, 65000));
        $this->assertSame(2, RotUnderlag::laborOre(1, 100));
        $this->assertSame(160000, RotUnderlag::materialOre('2', 80000));
        $this->assertSame(200000, RotUnderlag::materialOre('2.50', 80000));
        $this->assertSame(0, RotUnderlag::materialOre('0.01', 1));
    }
}
