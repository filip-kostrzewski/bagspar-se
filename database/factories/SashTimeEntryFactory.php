<?php

namespace Database\Factories;

use App\Models\SashTimeEntry;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SashTimeEntry>
 */
class SashTimeEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sash_id' => WindowSash::factory(),
            'user_id' => User::factory(),
            'user_name' => fake()->name(),
            'worked_on' => '2026-10-16',
            'minutes' => 45,
            'note' => null,
        ];
    }
}
