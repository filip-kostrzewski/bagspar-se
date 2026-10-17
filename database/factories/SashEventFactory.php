<?php

namespace Database\Factories;

use App\Models\SashEvent;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SashEvent>
 */
class SashEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sash_id' => WindowSash::factory(),
            'user_id' => User::factory(),
            'user_name' => fake()->name(),
            'kind' => SashEvent::KIND_STAGE,
            'stage' => 'received',
            'body' => null,
        ];
    }

    public function note(?string $body = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'kind' => SashEvent::KIND_NOTE,
            'stage' => null,
            'body' => $body ?? fake()->sentence(),
        ]);
    }
}
