@extends('layouts.workshop')

@section('title', 'Material: '.$project->name.' — Bågspår')
@section('body_class', 'workshop-print-report')

@section('content')
    <div class="workspace-page report-page">
        <nav class="workshop-breadcrumbs no-print" aria-label="Brödsmulor">
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Material</span>
        </nav>

        <header class="report-header">
            <div>
                <p class="workshop-eyebrow">MATERIAL</p>
                <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
            </div>
            <div class="report-header__actions no-print">
                <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Till projektet</a>
                <button class="workshop-button workshop-button--primary" type="button" data-print-report>Skriv ut / spara som PDF</button>
            </div>
        </header>

        @if ($lines === [])
            <p>Inget material är registrerat i projektet.</p>
        @else
            <table class="material-sheet">
                <caption class="visually-hidden">Material i {{ $project->name }}</caption>
                <thead>
                    <tr>
                        <th scope="col">Typ</th>
                        <th scope="col">Beskrivning</th>
                        <th scope="col">Mängd</th>
                        <th scope="col">Enhet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $line)
                        <tr>
                            <td>{{ $line['label'] }}</td>
                            <td>{{ $line['description'] }}</td>
                            <td>{{ $line['quantity_label'] }}</td>
                            <td>{{ $line['unit'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
