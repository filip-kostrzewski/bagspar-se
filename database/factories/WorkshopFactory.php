<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Support\PilotPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'trial_ends_at' => PilotPeriod::forAccountCreatedAt(now()),
        ];
    }
}
