<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SashPhoto;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ClientStatusController extends Controller
{
    public function show(string $token): Response
    {
        $project = $this->projectForToken($token);
        $workshop = $project->workshop()->firstOrFail();
        $sashes = $project->windowSashes()
            ->with(['photos' => fn (HasMany $query) => $query
                ->whereIn('moment', SashPhoto::MOMENTS)
                ->orderBy('created_at')
                ->orderBy('id')])
            ->orderBy('code')
            ->get();

        return $this->publicResponse(view('client-status.show', compact('project', 'workshop', 'sashes', 'token')));
    }

    public function photo(string $token, SashPhoto $photo): BinaryFileResponse
    {
        $project = $this->projectForToken($token);
        $photo = SashPhoto::query()->whereKey($photo->getKey())->firstOrFail();
        abort_unless(in_array($photo->moment, SashPhoto::MOMENTS, true), 404);

        $sash = $photo->sash()->first();
        abort_unless($sash !== null && $sash->project_id === $project->getKey(), 404);

        return $this->privateFile($photo->path, $photo->mime_type ?: 'image/webp');
    }

    public function logo(string $token): BinaryFileResponse
    {
        $project = $this->projectForToken($token);
        $workshop = $project->workshop()->firstOrFail();
        $path = $workshop->logo_path;
        $prefix = 'workshop-logos/'.$workshop->getKey().'/';
        abort_unless(is_string($path) && str_starts_with($path, $prefix), 404);

        return $this->privateFile($path, 'image/webp');
    }

    private function projectForToken(string $token): Project
    {
        return Project::query()
            ->where('client_status_token', $token)
            ->where('client_status_enabled', true)
            ->firstOrFail();
    }

    private function publicResponse(View $view): Response
    {
        return response($view)
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store');
    }

    private function privateFile(string $path, string $contentType): BinaryFileResponse
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        $response = response()->file($disk->path($path), [
            'Content-Type' => $contentType,
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }
}
