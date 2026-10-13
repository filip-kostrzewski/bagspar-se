<?php

namespace Database\Factories;

use App\Models\PilotLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PilotLead>
 */
class PilotLeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'message' => fake()->optional()->sentence(),
            'consented_at' => now(),
        ];
    }
}
