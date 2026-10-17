<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use App\Models\Workshop;
use App\Support\EmbeddedImage;
use App\Support\PdfDocument;
use App\Support\WorkshopLogo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class IntakeProtocolController extends Controller
{
    private const PDF_PHOTOS_PER_SASH = 4;

    public function show(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);

        return view('app.projects.intake', $this->data($workshop, $project));
    }

    public function pdf(Request $request, Project $project): Response
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $data = $this->data($workshop, $project);
        $embeds = [];
        $hiddenPhotos = [];

        foreach ($data['sashes'] as $sash) {
            $beforePhotos = $sash->photos->filter(fn (SashPhoto $photo): bool => $photo->isBefore())->values();
            $hiddenPhotos[$sash->getKey()] = max(0, $beforePhotos->count() - self::PDF_PHOTOS_PER_SASH);

            foreach ($beforePhotos->take(self::PDF_PHOTOS_PER_SASH) as $photo) {
                $uri = EmbeddedImage::jpegDataUri($photo->path, 'sash-photos/');

                if (is_string($uri)) {
                    $embeds[$photo->getKey()] = $uri;
                }
            }
        }

        $data['photoEmbeds'] = $embeds;
        $data['hiddenPhotos'] = $hiddenPhotos;
        $data['pdfPhotosPerSash'] = self::PDF_PHOTOS_PER_SASH;
        $data['logoEmbed'] = WorkshopLogo::visible($workshop) === null
            ? null
            : EmbeddedImage::jpegDataUri($workshop->logo_path, 'workshop-logos/'.$workshop->getKey().'/');
        $html = view('app.projects.intake-pdf', $data)->render();

        return PdfDocument::download($html, 'mottagningsprotokoll-'.$project->name.'.pdf');
    }

    public function approve(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('update', $project);

        $name = $request->input('name');

        if (is_string($name)) {
            $request->merge(['name' => trim($name)]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'Ange kundens namn.',
            'name.string' => 'Ange kundens namn som text.',
            'name.max' => 'Namnet får vara högst 150 tecken.',
        ]);

        $saved = Project::query()
            ->whereKey($project->getKey())
            ->whereNull('intake_approved_at')
            ->update([
                'intake_approved_at' => now(),
                'intake_approved_name' => $validated['name'],
                'intake_approved_via' => 'on-site',
            ]);

        if ($saved === 0) {
            return back()->with('status', 'Mottagningsprotokollet är redan godkänt.');
        }

        $actor = $request->user();

        if ($actor instanceof User) {
            $project->recordEvent(
                $actor,
                ProjectEvent::KIND_INTAKE_APPROVED,
                'Mottagningsprotokollet godkändes på plats av '.$validated['name'].'.',
            );
        }

        return back()->with('status', 'Mottagningsprotokollet är godkänt.');
    }

    public function destroyApproval(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('update', $workshop);

        $project->forceFill([
            'intake_approved_at' => null,
            'intake_approved_name' => null,
            'intake_approved_via' => null,
        ])->save();

        return back()->with('status', 'Godkännandet har tagits bort.');
    }

    /**
     * @return array{workshop: Workshop, project: Project, sashes: Collection<int, WindowSash>}
     */
    private function data(Workshop $workshop, Project $project): array
    {
        $project->load('customer');
        $sashes = $project->windowSashes()
            ->with(['photos' => fn (HasMany $query) => $query->inDocumentOrder()])
            ->orderBy('code')
            ->get();

        return [
            'workshop' => $workshop,
            'project' => $project,
            'sashes' => $sashes,
        ];
    }
}
