<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class PrivateImageResponse
{
    public static function make(string $path, string $contentType): Response
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        $bytes = $disk->get($path);
        abort_if(! is_string($bytes) || $bytes === '', 404);

        return response($bytes, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
