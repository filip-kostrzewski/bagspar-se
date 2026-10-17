<?php

namespace App\Support;

use App\Models\Project;
use App\Models\SashTimeEntry;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProjectReport
{
    /**
     * @return array<string, mixed>
     */
    public static function data(Workshop $workshop, Project $project, bool $includeWorkNotes, bool $includeWorkTime): array
    {
        $project->load('customer');
        $sashes = $project->windowSashes()
            ->with(['photos' => fn (HasMany $query) => $query->inDocumentOrder()])
            ->orderBy('code')
            ->get();
        $clientStatusUrl = $project->clientStatusIsPublic()
            ? route('client-status.show', ['token' => $project->client_status_token])
            : null;
        $clientStatusQr = null;

        if (is_string($clientStatusUrl)) {
            $clientStatusQr = preg_replace(
                '/^<\?xml[^>]*>\s*/',
                '',
                app(SashQrCodeRenderer::class)->render($clientStatusUrl),
            );
        }

        $timeEntries = SashTimeEntry::query()
            ->whereIn('sash_id', $sashes->modelKeys())
            ->orderBy('user_name')
            ->orderBy('id')
            ->get();
        $timeByPerson = $timeEntries
            ->groupBy('user_name')
            ->map(fn ($rows): int => (int) $rows->sum('minutes'))
            ->sortKeys();

        return [
            'workshop' => $workshop,
            'project' => $project,
            'sashes' => $sashes,
            'clientStatusUrl' => $clientStatusUrl,
            'clientStatusQr' => $clientStatusQr,
            'includeWorkNotes' => $includeWorkNotes,
            'includeWorkTime' => $includeWorkTime,
            'timeTotalMinutes' => (int) $timeEntries->sum('minutes'),
            'timeEntries' => $timeEntries,
            'timeByPerson' => $timeByPerson,
        ];
    }
}
