<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class StoredSashPhoto
{
    public static function delete(mixed $path): void
    {
        if (! is_string($path) || ! str_starts_with($path, 'sash-photos/') || str_contains($path, '..')) {
            return;
        }

        Storage::disk('local')->delete($path);
    }
}
