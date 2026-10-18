<?php

namespace Database\Factories;

use App\Models\SpamSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpamSubmission>
 */
class SpamSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reason' => 'gibberish',
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'payload' => [
                'contact_name' => fake()->name(),
                'contact_company' => null,
                'contact_email' => fake()->safeEmail(),
                'contact_phone' => null,
                'contact_message' => fake()->sentence(),
                'source' => 'page',
                'website' => null,
            ],
        ];
    }
}
