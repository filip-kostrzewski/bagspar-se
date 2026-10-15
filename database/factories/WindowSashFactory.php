<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\WindowSash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WindowSash>
 */
class WindowSashFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'code' => strtoupper(fake()->unique()->bothify('A-###')),
            'room' => fake()->optional()->word(),
            'location' => fake()->optional()->word(),
            'stage' => 'received',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
