<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectEvent>
 */
class ProjectEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'user_name' => fake()->name(),
            'kind' => ProjectEvent::KIND_SASH_REMOVED,
            'body' => 'Bågen B-1 togs bort.',
        ];
    }
}
