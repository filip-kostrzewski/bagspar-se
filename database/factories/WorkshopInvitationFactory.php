<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkshopInvitation>
 */
class WorkshopInvitationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'invited_by_user_id' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'token_hash' => WorkshopInvitation::hashToken(WorkshopInvitation::freshToken()),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
        ];
    }
}
