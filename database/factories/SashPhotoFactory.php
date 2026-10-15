<?php

namespace Database\Factories;

use App\Models\SashPhoto;
use App\Models\WindowSash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SashPhoto>
 */
class SashPhotoFactory extends Factory
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
            'path' => 'sash-photos/'.fake()->uuid().'.webp',
            'original_filename' => 'window.webp',
            'mime_type' => 'image/webp',
            'size' => 1024,
        ];
    }
}
