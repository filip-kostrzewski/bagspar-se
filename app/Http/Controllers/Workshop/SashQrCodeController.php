<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WindowSash;
use App\Support\SashQrCodeRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SashQrCodeController extends Controller
{
    public function __invoke(Request $request, Project $project, WindowSash $sash, SashQrCodeRenderer $renderer): Response
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);

        return response($renderer->render(route('app.sashes.show', [$project, $sash])), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
