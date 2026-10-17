<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <title>ROT-underlag {{ $project->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #17313b; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        h2 { font-size: 14px; margin: 16px 0 6px; }
        p { margin: 0 0 6px; }
        .notice { border: 1px solid #17313b; padding: 8px; margin: 10px 0 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border-bottom: 1px solid #d6dad6; text-align: left; padding: 3px 6px 3px 0; vertical-align: top; }
    </style>
</head>
<body>
    <p>{{ $workshop->name }}</p>
    <h1>ROT-underlag</h1>
    <p>{{ $project->name }}</p>
    <div class="notice">
        <strong>{{ \App\Support\RotUnderlag::HEADING }}</strong>
        <p>{{ \App\Support\RotUnderlag::BODY }}</p>
    </div>
    <p>
        @if ($basis['hourly_rate_ore'] === null)
            Timpris saknas. Arbetskostnaden är inte räknad.
        @else
            Timpris {{ \App\Support\Kronor::format($basis['hourly_rate_ore']) }}. Varje tidsrad är avrundad till öre.
        @endif
    </p>
    @include('partials.workshop.rot-amounts')
    <h2>Arbetskostnad</h2>
    @if ($basis['labor_ore'] !== null)
        <p>Totalt {{ \App\Support\WorkDuration::label($basis['labor_minutes']) }}, {{ \App\Support\Kronor::format($basis['labor_ore']) }} exkl. moms.</p>
    @else
        <p>Totalt {{ \App\Support\WorkDuration::label($basis['labor_minutes']) }}.</p>
    @endif
    @if ($basis['labor_rows'] !== [])
        <table>
            <thead>
                <tr><th>Datum</th><th>Båge</th><th>Person</th><th>Tid</th><th>Anteckning</th><th>Belopp exkl. moms</th></tr>
            </thead>
            <tbody>
                @foreach ($basis['labor_rows'] as $row)
                    <tr>
                        <td>{{ \App\Support\SwedishDate::format($row['date']) }}</td>
                        <td>{{ $row['sash'] }}</td>
                        <td>{{ $row['person'] }}</td>
                        <td>{{ \App\Support\WorkDuration::label($row['minutes']) }}</td>
                        <td>{{ $row['note'] }}</td>
                        <td>{{ $row['ore'] === null ? 'Timpris saknas' : \App\Support\Kronor::format($row['ore']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <h2>Material</h2>
    <p>Material {{ \App\Support\Kronor::format($basis['material_ore']) }} exkl. moms.</p>
    @if ($basis['unpriced_material_count'] > 0)
        <p>Rader utan pris ingår inte i materialsumman.</p>
    @endif
    @if ($basis['material_rows'] !== [])
        <table>
            <thead>
                <tr><th>Båge</th><th>Material</th><th>Mängd</th><th>À-pris exkl. moms</th><th>Belopp exkl. moms</th></tr>
            </thead>
            <tbody>
                @foreach ($basis['material_rows'] as $row)
                    <tr>
                        <td>{{ $row['sash'] }}</td>
                        <td>{{ $row['label'] }}, {{ $row['description'] }}</td>
                        <td>{{ $row['quantity'] }} {{ $row['unit'] }}</td>
                        <td>{{ $row['unit_price_ore'] === null ? 'Pris saknas' : \App\Support\Kronor::format($row['unit_price_ore']) }}</td>
                        <td>{{ $row['ore'] === null ? 'Pris saknas' : \App\Support\Kronor::format($row['ore']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <p>Bågspår · {{ \App\Support\SwedishDate::format(now()->timezone('Europe/Stockholm')) }}</p>
</body>
</html>
