<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SashMaterial;
use App\Models\SashTimeEntry;
use App\Models\User;
use App\Models\Workshop;
use App\Support\Kronor;
use App\Support\PdfDocument;
use App\Support\RotUnderlag;
use App\Support\SwedishDate;
use App\Support\WorkDuration;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectRotController extends Controller
{
    public function show(Request $request, Project $project): View
    {
        [$workshop, $project, $basis] = $this->basis($request, $project);

        return view('app.projects.rot', [
            'workshop' => $workshop,
            'project' => $project,
            'basis' => $basis,
            'canEditRate' => $request->user() instanceof User && $request->user()->isOwner(),
        ]);
    }

    public function csv(Request $request, Project $project): StreamedResponse
    {
        [, $project, $basis] = $this->basis($request, $project);
        $filename = PdfDocument::filename($project->name.'-rot-underlag.pdf');
        $filename = str_replace('.pdf', '.csv', $filename);

        return response()->streamDownload(function () use ($basis): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [RotUnderlag::HEADING.'. '.RotUnderlag::BODY], ';', '"', '\\');
            fputcsv($handle, ['Radtyp', 'Datum', 'Båge', 'Person eller material', 'Tid eller mängd', 'Anteckning', 'Pris (kr)', 'Belopp (kr)'], ';', '"', '\\');

            foreach ($basis['labor_rows'] as $row) {
                fputcsv($handle, [
                    'Arbetstid',
                    SwedishDate::format($row['date']),
                    $row['sash'],
                    $row['person'],
                    WorkDuration::label($row['minutes']),
                    $row['note'],
                    $basis['hourly_rate_ore'] === null ? '' : Kronor::amount($basis['hourly_rate_ore']),
                    $row['ore'] === null ? '' : Kronor::amount($row['ore']),
                ], ';', '"', '\\');
            }

            foreach ($basis['material_rows'] as $row) {
                fputcsv($handle, [
                    'Material',
                    '',
                    $row['sash'],
                    $row['label'].', '.$row['description'],
                    $row['quantity'].' '.$row['unit'],
                    $row['ore'] === null ? 'Pris saknas' : '',
                    $row['unit_price_ore'] === null ? '' : Kronor::amount($row['unit_price_ore']),
                    $row['ore'] === null ? '' : Kronor::amount($row['ore']),
                ], ';', '"', '\\');
            }

            fputcsv($handle, [
                'Summering',
                '',
                '',
                'Arbetskostnad',
                WorkDuration::label($basis['labor_minutes']),
                $basis['labor_ore'] === null ? 'Timpris saknas' : '',
                '',
                $basis['labor_ore'] === null ? '' : Kronor::amount($basis['labor_ore']),
            ], ';', '"', '\\');
            fputcsv($handle, [
                'Summering',
                '',
                '',
                'Material',
                '',
                $basis['unpriced_material_count'] > 0 ? 'Rader utan pris ingår inte' : '',
                '',
                Kronor::amount($basis['material_ore']),
            ], ';', '"', '\\');

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function pdf(Request $request, Project $project): Response
    {
        [$workshop, $project, $basis] = $this->basis($request, $project);
        $html = view('app.projects.rot-pdf', [
            'workshop' => $workshop,
            'project' => $project,
            'basis' => $basis,
        ])->render();

        return PdfDocument::download($html, PdfDocument::filename($project->name.'-rot-underlag.pdf'));
    }

    /**
     * @return array{0: Workshop, 1: Project, 2: array<string, mixed>}
     */
    private function basis(Request $request, Project $project): array
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $entries = SashTimeEntry::query()
            ->with('sash')
            ->whereHas('sash', fn ($query) => $query->where('project_id', $project->getKey()))
            ->orderBy('worked_on')
            ->orderBy('id')
            ->get();
        $materials = SashMaterial::query()
            ->with('sash')
            ->whereHas('sash', fn ($query) => $query->where('project_id', $project->getKey()))
            ->orderBy('id')
            ->get();

        return [$workshop, $project, RotUnderlag::make($entries, $materials, $workshop->hourly_rate_ore)];
    }
}
