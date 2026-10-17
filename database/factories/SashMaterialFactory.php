<?php

namespace Database\Factories;

use App\Models\SashMaterial;
use App\Models\WindowSash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SashMaterial>
 */
class SashMaterialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sash_id' => WindowSash::factory(),
            'type' => 'glas',
            'description' => 'Energiglas',
            'quantity' => '1.50',
            'unit' => 'm²',
        ];
    }
}
