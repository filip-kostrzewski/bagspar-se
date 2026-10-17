<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SashPhoto;
use App\Support\PrivateImageResponse;
use App\Support\WorkshopLogo;
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
        $image = WorkshopLogo::visible($this->projectForToken($token)->workshop()->firstOrFail());
        abort_if($image === null, 404);

        return PrivateImageResponse::fromBytes($image['bytes'], $image['mime']);
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
