<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'name' => fake()->company(),
            'contact_name' => fake()->name(),
            'address' => fake()->streetAddress(),
            'email' => fake()->safeEmail(),
        ];
    }
}
