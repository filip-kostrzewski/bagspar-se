<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <title>Offert {{ $project->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #17313b; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        h2 { font-size: 14px; margin: 16px 0 6px; }
        p { margin: 0 0 6px; }
        .notice { border: 1px solid #17313b; padding: 8px; margin: 10px 0 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border-bottom: 1px solid #d6dad6; text-align: left; padding: 3px 6px 3px 0; vertical-align: top; }
        .num { text-align: right; }
        .totals td, .totals th { border-bottom: 0; }
        .grand th, .grand td { border-top: 1px solid #17313b; font-weight: bold; }
    </style>
</head>
<body>
    <p>{{ $workshop->name }}@if ($workshop->organization_number) · Org.nr {{ $workshop->organization_number }}@endif</p>
    <h1>Offert</h1>
    <p>{{ $project->name }}@if ($project->customerLabel()) · {{ $project->customerLabel() }}@endif</p>
    @if (is_string($quote->introduction) && $quote->introduction !== '')
        <p>{!! nl2br(e($quote->introduction)) !!}</p>
    @endif
    <table>
        <thead>
            <tr><th>Beskrivning</th><th class="num">Antal</th><th class="num">À-pris exkl. moms</th><th class="num">Summa exkl. moms</th></tr>
        </thead>
        <tbody>
            @foreach ($quote->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ $line->quantity }}</td>
                    <td class="num">{{ \App\Support\Kronor::format($line->unit_price_ore) }}</td>
                    <td class="num">{{ \App\Support\Kronor::format($line->lineTotalOre()) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="totals">
        <tbody>
            <tr><th class="num">Summa exkl. moms</th><td class="num">{{ \App\Support\Kronor::format($quote->subtotalOre()) }}</td></tr>
            <tr><th class="num">Moms 25 %</th><td class="num">{{ \App\Support\Kronor::format($quote->vatOre()) }}</td></tr>
            <tr class="grand"><th class="num">Att betala inkl. moms</th><td class="num">{{ \App\Support\Kronor::format($quote->totalOre()) }}</td></tr>
        </tbody>
    </table>
    @if ($quote->valid_until)
        <p>Offerten gäller till {{ \App\Support\SwedishDate::format($quote->valid_until) }}.</p>
    @endif
    <div class="notice">
        <p>Priset gäller före eventuellt ROT-avdrag, som görs på fakturan enligt Skatteverkets regler.</p>
    </div>
    @if ($quote->isAccepted())
        <p>Godkänd {{ \App\Support\SwedishDate::formatDateTime($quote->accepted_at) }} av {{ $quote->accepted_name }}.</p>
    @endif
    <p>Bågspår · {{ \App\Support\SwedishDate::format(now()->timezone('Europe/Stockholm')) }}</p>
</body>
</html>
