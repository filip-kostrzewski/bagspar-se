@extends('layouts.workshop')

@section('title', 'Rapport: '.$project->name.' — Bågspår')
@section('body_class', 'workshop-print-report')

@section('content')
    @php
        $projectIsComplete = $project->status === 'completed';
        $sashCount = $sashes->count();
    @endphp
    <div class="workspace-page report-page">
        <nav class="workshop-breadcrumbs no-print" aria-label="Brödsmulor">
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Rapport</span>
        </nav>

        <header class="report-header">
            <div class="report-header__brand">
                <img src="{{ asset('images/bagspar-mark.svg') }}" alt="" width="42" height="42">
                <span>Bågspår</span>
            </div>
            <div class="report-header__actions no-print">
                <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Till projektet</a>
                <button class="workshop-button workshop-button--primary" type="button" data-print-report>Skriv ut / spara som PDF</button>
            </div>
        </header>

        <section class="report-project" aria-labelledby="page-title">
            <div class="report-project__title-row">
                <div>
                    <p class="workshop-eyebrow">PROJEKTRAPPORT</p>
                    <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
                </div>
                <span class="project-card__status @if ($projectIsComplete) project-card__status--complete @endif">
                    <span class="status-dot" aria-hidden="true"></span>
                    {{ $projectIsComplete ? 'Avslutat' : 'Pågår' }}
                </span>
            </div>
            <dl class="report-facts">
                @if ($project->customer_name)
                    <div><dt>Kund</dt><dd>{{ $project->customer_name }}</dd></div>
                @endif
                @if ($project->location)
                    <div><dt>Plats</dt><dd>{{ $project->location }}</dd></div>
                @endif
                <div><dt>Antal bågar</dt><dd>{{ $sashCount }}</dd></div>
                @if ($project->created_at)
                    <div><dt>Skapat</dt><dd>{{ $project->created_at->format('Y-m-d') }}</dd></div>
                @endif
            </dl>
            @if ($project->notes)
                <div class="report-project__notes">
                    <h2>Projektanteckningar</h2>
                    <p>{{ $project->notes }}</p>
                </div>
            @endif
        </section>

        @if ($sashes->isEmpty())
            <section class="report-empty" aria-label="Arbetskort">
                <h2>Inga arbetskort i projektet ännu</h2>
                <p>När bågar har lagts till visas deras uppgifter och arbetssteg här.</p>
            </section>
        @else
            <section class="report-sashes" aria-labelledby="report-sashes-title">
                <h2 class="workshop-section-heading" id="report-sashes-title">Arbetskort</h2>
                @foreach ($sashes as $sash)
                    <article class="report-sash">
                        <header class="report-sash__heading">
                            <div>
                                <p class="workshop-eyebrow">BÅGE</p>
                                <h3>{{ $sash->code }}</h3>
                                <p class="report-sash__location">{{ collect([$sash->room, $sash->location])->filter()->join(' · ') ?: 'Plats inte angiven' }}</p>
                            </div>
                            <span class="report-sash__stage">
                                @include('partials.workshop.stage-icon', ['stage' => $sash->stage])
                                @include('partials.workshop.stage-label', ['stage' => $sash->stage])
                            </span>
                        </header>
                        @include('partials.workshop.stage-tracker', ['currentStage' => $sash->stage])
                        @if ($sash->notes)
                            <div class="report-sash__notes">
                                <h4>Arbetsanteckningar</h4>
                                <p>{{ $sash->notes }}</p>
                            </div>
                        @endif
                        @if ($sash->photos->isNotEmpty())
                            <div class="report-photo-grid" aria-label="Bilder för båge {{ $sash->code }}">
                                @foreach ($sash->photos as $photo)
                                    <figure>
                                        <img src="{{ route('app.sashes.photos.show', [$project, $sash, $photo]) }}" alt="{{ $photo->original_filename ?? 'Foto' }} för båge {{ $sash->code }}" loading="eager">
                                        <figcaption>{{ $photo->original_filename ?? 'Foto' }}</figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif

        <footer class="report-footer">
            <span>{{ $workshop->name }}</span>
            <span>Rapport från Bågspår · {{ now()->format('Y-m-d') }}</span>
        </footer>
    </div>
@endsection
