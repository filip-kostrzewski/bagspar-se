<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <title>Mottagningsprotokoll {{ $project->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #17313b; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 18px 0 6px; }
        h3 { font-size: 13px; margin: 0; }
        p { margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border-bottom: 1px solid #d6dad6; text-align: left; padding: 4px 6px 4px 0; vertical-align: top; }
        .muted { color: #516269; }
        .sash { margin-top: 14px; page-break-inside: avoid; }
        .photo { margin: 6px 8px 0 0; }
        img { max-width: 220px; }
    </style>
</head>
<body>
    <p class="muted">{{ $workshop->name }}</p>
    @if ($workshop->organization_number)
        <p class="muted">Org.nr {{ $workshop->organization_number }}</p>
    @endif
    @if ($workshop->contact_email)
        <p class="muted">{{ $workshop->contact_email }}</p>
    @endif
    @if (is_string($workshop->phone) && $workshop->phone !== '')
        <p class="muted">{{ \App\Support\SwedishPhone::format($workshop->phone) }}</p>
    @endif
    @if ($workshop->address)
        <p class="muted">{{ $workshop->address }}</p>
    @endif
    @if (is_string($logoEmbed))
        <p><img src="{{ $logoEmbed }}" alt="" width="64" height="64"></p>
    @endif

    <h1>{{ $project->name }}</h1>
    <p class="muted">Mottagningsprotokoll</p>

    <table>
        <tbody>
            @if ($project->customerLabel())
                <tr><th>Kund</th><td>{{ $project->customerLabel() }}</td></tr>
            @endif
            @if ($project->location)
                <tr><th>Plats</th><td>{{ $project->location }}</td></tr>
            @endif
            @if ($project->created_at)
                <tr><th>Mottaget</th><td>{{ \App\Support\SwedishDate::format($project->created_at->copy()->timezone('Europe/Stockholm')) }}</td></tr>
            @endif
            <tr><th>Antal bågar</th><td>{{ $sashes->count() }}</td></tr>
        </tbody>
    </table>

    <p style="margin-top: 10px;">Protokollet beskriver bågarnas skick när de togs emot av verkstaden.</p>

    <h2>Bågar</h2>
    @forelse ($sashes as $sash)
        <div class="sash">
            <h3>{{ $sash->code }}</h3>
            <p>{{ collect([$sash->room, $sash->location])->filter()->join(' · ') ?: 'Plats inte angiven' }}</p>
            <p>Skick vid mottagning: {{ is_string($sash->intake_notes) && trim($sash->intake_notes) !== '' ? $sash->intake_notes : 'Ingen anmärkning' }}</p>
            @foreach ($sash->photos->filter(fn ($photo): bool => $photo->isBefore())->take($pdfPhotosPerSash) as $photo)
                <div class="photo">
                    @if (isset($photoEmbeds[$photo->getKey()]))
                        <img src="{{ $photoEmbeds[$photo->getKey()] }}" alt="{{ $photo->documentCaption() }}">
                    @endif
                    <p>{{ $photo->documentCaption() }}</p>
                </div>
            @endforeach
            @if (($hiddenPhotos[$sash->getKey()] ?? 0) > 0)
                <p class="muted">+{{ $hiddenPhotos[$sash->getKey()] }} bilder</p>
            @endif
        </div>
    @empty
        <p>Inga bågar i projektet ännu.</p>
    @endforelse

    <h2>Godkännande</h2>
    @if ($project->intakeIsApproved())
        <p>{{ $project->intakeApprovalLabel() }}</p>
    @else
        <p>Inte godkänt av kunden.</p>
    @endif

    <p class="muted" style="margin-top: 14px;">Bågspår · {{ \App\Support\SwedishDate::format(now()->timezone('Europe/Stockholm')) }}</p>
</body>
</html>
