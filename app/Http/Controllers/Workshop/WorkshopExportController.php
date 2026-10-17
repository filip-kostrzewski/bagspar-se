<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WindowSash;
use App\Support\SwedishDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkshopExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $workshop = $this->currentWorkshop($request);
        Gate::authorize('update', $workshop);

        $projects = $workshop->projects()
            ->with(['windowSashes.timeEntries', 'windowSashes.materials'])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return response()->streamDownload(function () use ($projects): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Projekt',
                'Kund',
                'Plats',
                'Projektstatus',
                'Arkiverad',
                'Båge',
                'Rum',
                'Placering',
                'Etapp',
                'Skapad',
                'Uppdaterad',
                'Arbetstid (minuter)',
                'Material',
            ], ';', '"', '\\');

            foreach ($projects as $project) {
                if ($project->windowSashes->isEmpty()) {
                    fputcsv($handle, $this->row($project, null), ';', '"', '\\');

                    continue;
                }

                foreach ($project->windowSashes->sortBy('code') as $sash) {
                    fputcsv($handle, $this->row($project, $sash), ';', '"', '\\');
                }
            }

            fclose($handle);
        }, 'bagspar-projekt.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return list<string>
     */
    private function row(Project $project, ?WindowSash $sash): array
    {
        $created = ($sash?->created_at ?? $project->created_at)?->copy()->timezone('Europe/Stockholm');
        $updated = ($sash?->updated_at ?? $project->updated_at)?->copy()->timezone('Europe/Stockholm');

        return [
            $this->cell($project->name),
            $this->cell($project->customerLabel() ?? ''),
            $this->cell($project->location ?? ''),
            $this->cell($project->status === 'completed' ? 'Avslutad' : 'Pågår'),
            $this->cell($project->isArchived() ? 'Ja' : 'Nej'),
            $this->cell($sash?->code ?? ''),
            $this->cell($sash?->room ?? ''),
            $this->cell($sash?->location ?? ''),
            $this->cell($sash?->stageLabel() ?? ''),
            $this->cell(SwedishDate::format($created)),
            $this->cell(SwedishDate::format($updated)),
            $this->cell($sash instanceof WindowSash ? (string) $sash->timeMinutes() : ''),
            $this->cell($sash instanceof WindowSash ? $sash->materialSummary() : ''),
        ];
    }

    private function cell(string $value): string
    {
        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
