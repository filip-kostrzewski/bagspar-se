<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class EmbeddedImage
{
    public static function jpegDataUri(?string $path, string $prefix): ?string
    {
        if (! is_string($path) || $path === '' || ! str_starts_with($path, $prefix) || str_contains($path, '..')) {
            return null;
        }

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $contents = Storage::disk('local')->get($path);

        if (! is_string($contents) || $contents === '') {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if (! $image instanceof \GdImage) {
            return null;
        }

        ob_start();
        $encoded = imagejpeg($image, null, 80);
        $jpeg = ob_get_clean();
        unset($image);

        if ($encoded !== true || ! is_string($jpeg) || $jpeg === '') {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }
}
