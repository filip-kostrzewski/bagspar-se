@extends('layouts.label-print')

@section('title', 'Etikett '.$sash->code.' — '.$project->name)

@section('body_class', 'label-print-single')

@section('print_style')
    <style>
        @page {
            size: 62mm 29mm;
            margin: 0;
        }

        html.label-print-single {
            page: label-62x29;
        }

        @media print {
            html.label-print-single,
            html.label-print-single body,
            html.label-print-single .label-print-main,
            html.label-print-single .label-preview,
            html.label-print-single .print-label {
                width: 62mm;
                height: 29mm;
                min-height: 0 !important;
                max-height: 29mm;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                background: #fff !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="label-toolbar no-print">
        <p class="workshop-eyebrow">ETIKETT 62 × 29 MM</p>
        <h1 class="label-toolbar__title">Skriv ut etikett</h1>
        <p>Etiketten visar bara QR-kod, bågens nummer, rum och projekt. Skanningen är till för verkstadens personal och kräver inloggning. Kundens status är en separat länk som verkstaden slår på för projektet.</p>
        <div class="label-toolbar__actions">
            <a class="workshop-button workshop-button--quiet" href="{{ route('app.sashes.show', [$project, $sash]) }}">Till arbetskortet</a>
            <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.labels', $project) }}">A4-ark för hela projektet</a>
            <button class="workshop-button workshop-button--primary" type="button" data-print-labels>Skriv ut etikett</button>
        </div>
        <p class="visually-hidden" role="status" aria-live="polite" data-label-print-status></p>
    </div>

    <div class="label-preview">
        @include('partials.workshop.print-label', ['project' => $project, 'sash' => $sash])
    </div>
@endsection
