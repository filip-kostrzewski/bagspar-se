@extends('layouts.label-print')

@section('title', 'Etiketter: '.$project->name)

@section('body_class', 'label-print-sheet')

@section('print_style')
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm;
        }

        html.label-print-sheet {
            page: label-a4;
        }

        @media print {
            html.label-print-sheet .label-print-main {
                min-height: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="label-toolbar no-print">
        <p class="workshop-eyebrow">A4 · 62 × 29 MM</p>
        <h1 class="label-toolbar__title">Etiketter för {{ $project->name }}</h1>
        <p>Varje ruta är 62 × 29 mm och visar QR-kod, bågens nummer, rum och projekt. Skanningen är till för verkstadens personal och kräver inloggning.</p>
        <div class="label-toolbar__actions">
            <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Till projektet</a>
            @if ($sashes->isNotEmpty())
                <button class="workshop-button workshop-button--primary" type="button" data-print-labels>Skriv ut A4-arket</button>
            @endif
        </div>
        <p class="visually-hidden" role="status" aria-live="polite" data-label-print-status></p>
    </div>

    @if ($sashes->isEmpty())
        <p class="label-sheet-empty">Det finns inga bågar att skriva ut etiketter för ännu.</p>
    @else
        <div class="label-sheet">
            @foreach ($sashes as $sash)
                @include('partials.workshop.print-label', ['project' => $project, 'sash' => $sash])
            @endforeach
        </div>
    @endif
@endsection
