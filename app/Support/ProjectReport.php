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
        $quote = $project->quote()->sentOrAccepted()->with('lines')->first();
        $clientStatusUrl = $project->clientStatusIsPublic()
            ? route('client-status.show', ['token' => $project->client_status_token])
            : null;
        $clientStatusQr = null;
        $clientStatusQrImage = null;

        if (is_string($clientStatusUrl)) {
            $qrCode = app(SashQrCodeRenderer::class);
            $clientStatusQr = preg_replace(
                '/^<\?xml[^>]*>\s*/',
                '',
                $qrCode->render($clientStatusUrl),
            );
            $clientStatusQrImage = $qrCode->renderPngDataUri($clientStatusUrl);
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
            'quote' => $quote,
            'clientStatusUrl' => $clientStatusUrl,
            'clientStatusQr' => $clientStatusQr,
            'clientStatusQrImage' => $clientStatusQrImage,
            'includeWorkNotes' => $includeWorkNotes,
            'includeWorkTime' => $includeWorkTime,
            'timeTotalMinutes' => (int) $timeEntries->sum('minutes'),
            'timeEntries' => $timeEntries,
            'timeByPerson' => $timeByPerson,
        ];
    }
}
