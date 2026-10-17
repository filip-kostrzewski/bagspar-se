<?php

namespace App\Support;

use App\Models\Workshop;
use GdImage;
use Illuminate\Support\Facades\Storage;

final class WorkshopLogo
{
    /**
     * @return array{bytes: string, mime: string}|null
     */
    public static function visible(Workshop $workshop): ?array
    {
        $path = $workshop->logo_path;
        $prefix = 'workshop-logos/'.$workshop->getKey().'/';

        if (! is_string($path) || ! str_starts_with($path, $prefix) || str_contains($path, '..')) {
            return null;
        }

        $bytes = Storage::disk('local')->get($path);

        if (! is_string($bytes) || $bytes === '') {
            return null;
        }

        $info = @getimagesizefromstring($bytes);
        $mime = is_array($info) ? ($info['mime'] ?? '') : '';

        if (! is_array($info) || ($info[0] ?? 0) < 1 || ($info[1] ?? 0) < 1 || ! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            return null;
        }

        $image = @imagecreatefromstring($bytes);

        if (! $image instanceof GdImage || ! self::hasVisiblePixel($image)) {
            return null;
        }

        return [
            'bytes' => $bytes,
            'mime' => $mime,
        ];
    }

    private static function hasVisiblePixel(GdImage $image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $stepX = max(1, intdiv($width, 40));
        $stepY = max(1, intdiv($height, 40));

        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));

                if (($rgba['alpha'] ?? 127) < 100) {
                    return true;
                }
            }
        }

        return false;
    }
}
