<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\EmbeddedImage;
use App\Support\PdfDocument;
use App\Support\ProjectReport;
use App\Support\WorkshopLogo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectReportPdfController extends Controller
{
    public function __invoke(Request $request, Project $project): Response
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $data = ProjectReport::data(
            $workshop,
            $project,
            $request->boolean('anteckningar'),
            $request->boolean('arbetstid'),
        );
        $embeds = [];

        foreach ($data['sashes'] as $sash) {
            foreach ($sash->photos as $photo) {
                $uri = EmbeddedImage::jpegDataUri($photo->path, 'sash-photos/');

                if (is_string($uri)) {
                    $embeds[$photo->getKey()] = $uri;
                }
            }
        }

        $data['photoEmbeds'] = $embeds;
        $data['logoEmbed'] = WorkshopLogo::visible($workshop) === null
            ? null
            : EmbeddedImage::jpegDataUri($workshop->logo_path, 'workshop-logos/'.$workshop->getKey().'/');
        $html = view('app.projects.report-pdf', $data)->render();
        $name = PdfDocument::filename($project->name.'-rapport.pdf');

        return PdfDocument::download($html, $name);
    }
}
