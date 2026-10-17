<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <title>Projektrapport {{ $project->name }}</title>
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
        .keep-together, .keep-together tr, .keep-together td { page-break-inside: avoid; }
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
    <p class="muted">Projektrapport</p>

    <table>
        <tbody>
            @if ($project->customerLabel())
                <tr><th>Kund</th><td>{{ $project->customerLabel() }}</td></tr>
            @endif
            @if ($project->customer?->phone)
                <tr><th>Telefon</th><td>{{ $project->customer->phone }}</td></tr>
            @endif
            @if ($project->location)
                <tr><th>Plats</th><td>{{ $project->location }}</td></tr>
            @endif
            @if ($project->delivery_date)
                <tr><th>Beräknad leverans</th><td>{{ \App\Support\SwedishDate::format($project->delivery_date) }}</td></tr>
            @endif
            @if ($project->delivery_approved_at)
                <tr>
                    <th>Leverans godkänd</th>
                    <td>
                        {{ \App\Support\SwedishDate::formatDateTime($project->delivery_approved_at) }}
                        @if (is_string($project->delivery_approved_name) && $project->delivery_approved_name !== '')
                            av {{ $project->delivery_approved_name }}
                        @endif
                    </td>
                </tr>
            @endif
            @if ($includeWorkTime)
                <tr><th>Arbetstid totalt</th><td>{{ \App\Support\WorkDuration::label($timeTotalMinutes) }}</td></tr>
            @endif
            <tr><th>Antal bågar</th><td>{{ $sashes->count() }}</td></tr>
        </tbody>
    </table>

    @if ($includeWorkTime && $timeByPerson->isNotEmpty())
        <h2>Arbetstid per person</h2>
        <table>
            <thead>
                <tr><th>Person</th><th>Tid</th></tr>
            </thead>
            <tbody>
                @foreach ($timeByPerson as $name => $minutes)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>{{ \App\Support\WorkDuration::label($minutes) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($includeWorkNotes && $project->notes)
        <h2>Projektanteckningar</h2>
        <p>{{ $project->notes }}</p>
    @endif

    <h2>Arbetskort</h2>
    @forelse ($sashes as $sash)
        <div class="sash">
            <h3>{{ $sash->code }}</h3>
            <p>{{ collect([$sash->room, $sash->location])->filter()->join(' · ') ?: 'Plats inte angiven' }}</p>
            <p>@include('partials.workshop.stage-label', ['stage' => $sash->stage])</p>
            @if ($includeWorkTime)
                @php
                    $sashTime = $timeEntries->where('sash_id', $sash->getKey());
                @endphp
                <p>Arbetstid {{ \App\Support\WorkDuration::label((int) $sashTime->sum('minutes')) }}</p>
                @foreach ($sashTime as $entry)
                    @if (is_string($entry->note) && $entry->note !== '')
                        <p>{{ $entry->user_name }}: {{ $entry->note }}</p>
                    @endif
                @endforeach
            @endif
            @if ($includeWorkNotes && $sash->notes)
                <p>{{ $sash->notes }}</p>
            @endif
            @foreach ($sash->photos as $photo)
                <div class="photo">
                    @if (isset($photoEmbeds[$photo->getKey()]))
                        <img src="{{ $photoEmbeds[$photo->getKey()] }}" alt="{{ $photo->documentCaption() }}">
                    @endif
                    <p>{{ $photo->documentCaption() }}</p>
                </div>
            @endforeach
        </div>
    @empty
        <p>Inga arbetskort i projektet ännu.</p>
    @endforelse

    @if (is_string($clientStatusUrl))
        <table class="keep-together" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td style="page-break-inside: avoid; height: 360px;">
                    <h2>Status för kunden</h2>
                    @if (is_string($clientStatusQrImage) && $clientStatusQrImage !== '')
                        <p>Skanna koden för att se etapperna och bilderna före och efter. Anteckningar ingår inte.</p>
                        <p><img src="{{ $clientStatusQrImage }}" alt="QR-kod till kundens status" width="160" height="160"></p>
                    @endif
                    <p><a href="{{ $clientStatusUrl }}">{{ $clientStatusUrl }}</a></p>
                    <p class="muted">Bågspår · {{ \App\Support\SwedishDate::format(now()->timezone('Europe/Stockholm')) }}</p>
                </td>
            </tr>
        </table>
    @else
        <p class="muted">Bågspår · {{ \App\Support\SwedishDate::format(now()->timezone('Europe/Stockholm')) }}</p>
    @endif
</body>
</html>
