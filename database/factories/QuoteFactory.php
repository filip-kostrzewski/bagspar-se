<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'workshop_id' => fn (array $attributes): mixed => Project::query()->whereKey($attributes['project_id'])->value('workshop_id'),
            'status' => Quote::STATUS_DRAFT,
            'introduction' => null,
            'valid_until' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Quote::STATUS_SENT,
            'token' => Str::random(48),
            'sent_at' => now(),
        ]);
    }

    public function accepted(): static
    {
        return $this->sent()->state(fn (array $attributes): array => [
            'status' => Quote::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'accepted_name' => fake()->name(),
        ]);
    }
}
