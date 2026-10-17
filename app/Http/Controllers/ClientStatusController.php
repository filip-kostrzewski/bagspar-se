<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SashPhoto;
use App\Support\PrivateImageResponse;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ClientStatusController extends Controller
{
    public function show(string $token): Response
    {
        $project = $this->projectForToken($token);
        $project->load('customer');
        $workshop = $project->workshop()->firstOrFail();
        $sashes = $project->windowSashes()
            ->with(['photos' => fn (HasMany $query) => $query->inDocumentOrder()])
            ->orderBy('code')
            ->get();

        return $this->publicResponse(view('client-status.show', compact('project', 'workshop', 'sashes', 'token')));
    }

    public function photo(string $token, SashPhoto $photo): Response
    {
        $project = $this->projectForToken($token);
        $photo = SashPhoto::query()->whereKey($photo->getKey())->firstOrFail();
        $sash = $photo->sash()->first();
        abort_unless($sash !== null && $sash->project_id === $project->getKey(), 404);

        return PrivateImageResponse::make($photo->path, $photo->mime_type ?: 'image/webp');
    }

    public function logo(string $token): Response
    {
        $project = $this->projectForToken($token);
        $workshop = $project->workshop()->firstOrFail();
        $path = $workshop->logo_path;
        $prefix = 'workshop-logos/'.$workshop->getKey().'/';
        abort_unless(is_string($path) && str_starts_with($path, $prefix), 404);

        return PrivateImageResponse::make($path, 'image/webp');
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
}
