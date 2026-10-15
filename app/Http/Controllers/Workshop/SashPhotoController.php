<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SashPhoto;
use App\Models\WindowSash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SashPhotoController extends Controller
{
    private const MAX_PHOTOS_PER_SASH = 20;

    private const MAX_IMAGE_PIXELS = 16_000_000;

    public function store(Request $request, Project $project, WindowSash $sash): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $validated = $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
        ], [
            'photo.required' => 'Välj en bild att ladda upp.',
            'photo.file' => 'Filen kunde inte läsas som en uppladdad bild.',
            'photo.image' => 'Filen måste vara en bild.',
            'photo.mimes' => 'Bilden måste vara i formatet JPG, PNG eller WebP.',
            'photo.max' => 'Bilden får vara högst 5 MB.',
            'photo.dimensions' => 'Bilden får vara högst 6 000 pixlar bred eller hög.',
        ]);

        $file = $validated['photo'];
        $imageInfo = @getimagesize($file->getRealPath());

        if (! is_array($imageInfo) || ! isset($imageInfo[0], $imageInfo[1], $imageInfo['mime'])) {
            throw ValidationException::withMessages(['photo' => 'Bilden kan inte läsas. Välj en annan fil.']);
        }

        $imageType = $imageInfo[2] ?? null;
        if (! in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages(['photo' => 'Bilden måste vara i formatet JPG, PNG eller WebP.']);
        }

        if ($imageInfo[0] * $imageInfo[1] > self::MAX_IMAGE_PIXELS) {
            throw ValidationException::withMessages(['photo' => 'Bilden innehåller för många bildpunkter. Välj en mindre bild.']);
        }

        $webp = $this->reencodeAsWebp($file->getRealPath(), $imageType);
        $path = null;

        try {
            DB::transaction(function () use ($file, $project, $sash, $webp, &$path): void {
                $lockedSash = WindowSash::query()->lockForUpdate()->findOrFail($sash->getKey());

                if ($lockedSash->photos()->count() >= self::MAX_PHOTOS_PER_SASH) {
                    throw ValidationException::withMessages(['photo' => 'Ett arbetskort kan ha högst 20 bilder.']);
                }

                $path = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/'.Str::uuid().'.webp';
                if (! Storage::disk('local')->put($path, $webp)) {
                    throw new RuntimeException('Private photo storage failed.');
                }

                $lockedSash->photos()->create([
                    'path' => $path,
                    'original_filename' => $this->safeOriginalFilename($file->getClientOriginalName()),
                    'mime_type' => 'image/webp',
                    'size' => strlen($webp),
                ]);
            });
        } catch (Throwable $exception) {
            if (is_string($path)) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return back()->with('status', 'Bilden har sparats på arbetskortet.');
    }

    public function show(Request $request, Project $project, WindowSash $sash, SashPhoto $photo): BinaryFileResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $photo = $sash->photos()->whereKey($photo->getKey())->firstOrFail();
        $disk = Storage::disk('local');

        abort_unless($disk->exists($photo->path), 404);

        $response = response()->file($disk->path($photo->path), [
            'Content-Type' => $photo->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }

    private function reencodeAsWebp(string $sourcePath, int $imageType): string
    {
        abort_unless(
            function_exists('imagecreatefromstring') && function_exists('imagewebp'),
            503,
            'Bildhanteringen är inte tillgänglig just nu.',
        );

        $orientation = $imageType === IMAGETYPE_JPEG ? $this->jpegOrientation($sourcePath) : 1;

        $contents = file_get_contents($sourcePath);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw ValidationException::withMessages(['photo' => 'Bilden kan inte öppnas. Välj en annan fil.']);
        }

        try {
            if ($imageType === IMAGETYPE_JPEG) {
                $image = $this->orientJpeg($image, $orientation);
            }

            if (! imageistruecolor($image) && ! imagepalettetotruecolor($image)) {
                throw ValidationException::withMessages(['photo' => 'Bilden kan inte bearbetas. Välj en annan fil.']);
            }

            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            $encoded = imagewebp($image, null, 82);
            $webp = ob_get_clean();

            if (! $encoded || ! is_string($webp) || $webp === '') {
                throw ValidationException::withMessages(['photo' => 'Bilden kan inte bearbetas. Välj en annan fil.']);
            }

            return $webp;
        } finally {
            imagedestroy($image);
        }
    }

    private function jpegOrientation(string $sourcePath): int
    {
        abort_unless(function_exists('exif_read_data'), 503, 'Bildens orientering kan inte läsas just nu.');

        $metadata = @exif_read_data($sourcePath, 'IFD0', true, false);
        $orientation = $metadata['IFD0']['Orientation'] ?? $metadata['Orientation'] ?? null;

        if (! is_numeric($orientation)) {
            return 1;
        }

        $orientation = (int) $orientation;

        return $orientation >= 2 && $orientation <= 8 ? $orientation : 1;
    }

    private function orientJpeg(\GdImage $image, int $orientation): \GdImage
    {
        if (in_array($orientation, [2, 5, 7], true) && ! imageflip($image, IMG_FLIP_HORIZONTAL)) {
            throw ValidationException::withMessages(['photo' => 'Bilden kan inte orienteras. Välj en annan fil.']);
        }

        if ($orientation === 4 && ! imageflip($image, IMG_FLIP_VERTICAL)) {
            throw ValidationException::withMessages(['photo' => 'Bilden kan inte orienteras. Välj en annan fil.']);
        }

        $rotation = match ($orientation) {
            3 => 180,
            5, 8 => 90,
            6, 7 => -90,
            default => null,
        };

        if ($rotation === null) {
            return $image;
        }

        $rotatedImage = imagerotate($image, $rotation, 0);
        if (! $rotatedImage instanceof \GdImage) {
            throw ValidationException::withMessages(['photo' => 'Bilden kan inte orienteras. Välj en annan fil.']);
        }

        imagedestroy($image);

        return $rotatedImage;
    }

    private function safeOriginalFilename(string $filename): ?string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename) ?? '';
        $filename = mb_substr(trim($filename), 0, 255);

        return $filename === '' ? null : $filename;
    }
}
