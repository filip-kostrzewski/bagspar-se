@extends('layouts.workshop')

@section('title', 'ROT-underlag: '.$project->name.' — Bågspår')

@section('content')
    <div class="workspace-page report-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">ROT-underlag</span>
        </nav>

        <header class="report-header">
            <div>
                <p class="workshop-eyebrow">UNDERLAG</p>
                <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
            </div>
            <div class="report-header__actions">
                <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Till projektet</a>
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.rot.csv', $project) }}">Ladda ner CSV</a>
                <a class="workshop-button workshop-button--primary" href="{{ route('app.projects.rot.pdf', $project) }}">Ladda ner PDF</a>
            </div>
        </header>

        <aside class="basis-notice" role="note">
            <strong>{{ \App\Support\RotUnderlag::HEADING }}</strong>
            <p>{{ \App\Support\RotUnderlag::BODY }}</p>
        </aside>

        <p>
            @if ($basis['hourly_rate_ore'] === null)
                Inget timpris är sparat. Arbetstiden visas, men arbetskostnaden kan inte räknas.
            @else
                Timpris {{ \App\Support\Kronor::format($basis['hourly_rate_ore']) }}. Varje tidsrad avrundas till öre, och arbetskostnaden är summan av raderna.
            @endif
            @if ($canEditRate)
                <a href="{{ route('app.settings.edit') }}">Ändra timpriset</a>
            @else
                Ägaren ändrar timpriset under verkstadens uppgifter.
            @endif
        </p>

        <section aria-labelledby="labor-title">
            <h2 class="workshop-section-heading" id="labor-title">Arbetskostnad</h2>
            <p>Totalt {{ \App\Support\WorkDuration::label($basis['labor_minutes']) }}@if ($basis['labor_ore'] !== null), {{ \App\Support\Kronor::format($basis['labor_ore']) }}@endif.</p>
            @if ($basis['people'] !== [])
                <table class="report-time">
                    <caption>Arbetstid per person</caption>
                    <thead>
                        <tr>
                            <th scope="col">Person</th>
                            <th scope="col">Tid</th>
                            <th scope="col">Belopp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($basis['people'] as $person)
                            <tr>
                                <th scope="row">{{ $person['name'] }}</th>
                                <td>{{ \App\Support\WorkDuration::label($person['minutes']) }}</td>
                                <td>{{ $person['ore'] === null ? 'Timpris saknas' : \App\Support\Kronor::format($person['ore']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if ($basis['labor_rows'] === [])
                <p>Ingen tid är registrerad i projektet.</p>
            @else
                <table class="material-sheet">
                    <caption>Tidsrader</caption>
                    <thead>
                        <tr>
                            <th scope="col">Datum</th>
                            <th scope="col">Båge</th>
                            <th scope="col">Person</th>
                            <th scope="col">Tid</th>
                            <th scope="col">Anteckning</th>
                            <th scope="col">Belopp</th>
                        </tr>
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
        </section>

        <section aria-labelledby="material-title">
            <h2 class="workshop-section-heading" id="material-title">Material</h2>
            <p>Material {{ \App\Support\Kronor::format($basis['material_ore']) }}.</p>
            @if ($basis['unpriced_material_count'] > 0)
                <p>{{ $basis['unpriced_material_count'] === 1 ? '1 rad saknar pris och ingår inte i materialsumman.' : $basis['unpriced_material_count'].' rader saknar pris och ingår inte i materialsumman.' }}</p>
            @endif
            @if ($basis['material_rows'] === [])
                <p>Inget material är registrerat i projektet.</p>
            @else
                <table class="material-sheet">
                    <caption>Materialrader</caption>
                    <thead>
                        <tr>
                            <th scope="col">Båge</th>
                            <th scope="col">Material</th>
                            <th scope="col">Mängd</th>
                            <th scope="col">À-pris</th>
                            <th scope="col">Belopp</th>
                        </tr>
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
        </section>
    </div>
@endsection
