<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'name' => fake()->sentence(3),
            'customer_name' => fake()->optional()->company(),
            'location' => fake()->optional()->address(),
            'notes' => fake()->optional()->paragraph(),
            'status' => 'active',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'completed',
        ]);
    }
}
