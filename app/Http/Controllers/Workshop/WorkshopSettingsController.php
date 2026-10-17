<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkshopSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('app.settings.edit', [
            'workshop' => $this->currentWorkshop($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $contactEmail = $request->input('contact_email');

        if (is_string($contactEmail)) {
            $request->merge(['contact_email' => Str::lower(trim($contactEmail))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'organization_number' => ['nullable', 'string', 'regex:/^\d{6}-\d{4}$/'],
            'contact_email' => ['nullable', 'string', 'email:rfc', 'max:254'],
            'address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2000,max_height=2000'],
            'remove_logo' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Ange verkstadens namn.',
            'name.string' => 'Ange verkstadens namn som text.',
            'name.max' => 'Verkstadens namn får vara högst 160 tecken.',
            'organization_number.regex' => 'Ange organisationsnumret med sex siffror, bindestreck och fyra siffror.',
            'contact_email.email' => 'Ange en giltig e-postadress.',
            'contact_email.max' => 'E-postadressen får vara högst 254 tecken.',
            'address.max' => 'Adressen får vara högst 255 tecken.',
            'logo.image' => 'Logotypen måste vara en bild.',
            'logo.mimes' => 'Logotypen måste vara i formatet JPG, PNG eller WebP.',
            'logo.max' => 'Logotypen får vara högst 2 MB.',
            'logo.dimensions' => 'Logotypen får vara högst 2 000 pixlar bred eller hög.',
        ]);

        $address = $validated['address'] ?? null;
        if (is_string($address)) {
            $address = trim($address);
            $address = $address === '' ? null : $address;
        }

        $previousLogoPath = $workshop->logo_path;
        $storedLogoPath = null;

        if ($request->hasFile('logo')) {
            $sourcePath = $validated['logo']->getRealPath();

            if (! is_string($sourcePath)) {
                throw ValidationException::withMessages([
                    'logo' => 'Logotypen kan inte öppnas. Välj en annan fil.',
                ]);
            }

            $storedLogoPath = $this->storeLogo($workshop, $sourcePath);
        }

        $workshop->fill([
            'name' => $validated['name'],
            'organization_number' => $validated['organization_number'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'address' => $address,
        ]);

        if (is_string($storedLogoPath)) {
            $workshop->logo_path = $storedLogoPath;
        } elseif ($request->boolean('remove_logo')) {
            $workshop->logo_path = null;
        }

        $workshop->save();

        if ($previousLogoPath !== $workshop->logo_path) {
            $this->deleteStoredLogo($previousLogoPath);
        }

        return back()->with('status', 'Verkstadens uppgifter har sparats.');
    }

    public function logo(Request $request): BinaryFileResponse
    {
        $workshop = $this->currentWorkshop($request);
        $path = $workshop->logo_path;
        $prefix = 'workshop-logos/'.$workshop->getKey().'/';
        $disk = Storage::disk('local');

        abort_unless(is_string($path) && str_starts_with($path, $prefix) && $disk->exists($path), 404);

        $response = response()->file($disk->path($path), [
            'Content-Type' => 'image/webp',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }

    private function storeLogo(Workshop $workshop, string $sourcePath): string
    {
        $webp = $this->reencodeLogo($sourcePath);
        $path = 'workshop-logos/'.$workshop->getKey().'/'.Str::uuid().'.webp';

        if (! Storage::disk('local')->put($path, $webp)) {
            throw ValidationException::withMessages([
                'logo' => 'Logotypen kunde inte sparas. Försök igen.',
            ]);
        }

        return $path;
    }

    private function reencodeLogo(string $sourcePath): string
    {
        abort_unless(function_exists('imagecreatefromstring') && function_exists('imagewebp'), 503, 'Bildhanteringen är inte tillgänglig just nu.');

        $contents = file_get_contents($sourcePath);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw ValidationException::withMessages([
                'logo' => 'Logotypen kan inte öppnas. Välj en annan fil.',
            ]);
        }

        try {
            if (! imageistruecolor($image) && ! imagepalettetotruecolor($image)) {
                throw ValidationException::withMessages([
                    'logo' => 'Logotypen kan inte bearbetas. Välj en annan fil.',
                ]);
            }

            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            $encoded = imagewebp($image, null, 82);
            $webp = ob_get_clean();

            if (! $encoded || ! is_string($webp) || $webp === '') {
                throw ValidationException::withMessages([
                    'logo' => 'Logotypen kan inte bearbetas. Välj en annan fil.',
                ]);
            }

            return $webp;
        } finally {
            imagedestroy($image);
        }
    }

    private function deleteStoredLogo(?string $path): void
    {
        if (! is_string($path) || ! str_starts_with($path, 'workshop-logos/')) {
            return;
        }

        Storage::disk('local')->delete($path);
    }
}
