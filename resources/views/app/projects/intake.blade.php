@extends('layouts.workshop')

@section('title', 'Mottagningsprotokoll: '.$project->name.' — Bågspår')
@section('body_class', 'workshop-print-report')

@section('content')
    <div class="workspace-page report-page">
        <nav class="workshop-breadcrumbs no-print" aria-label="Brödsmulor">
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Mottagningsprotokoll</span>
        </nav>

        <header class="report-header">
            <div class="report-workshop">
                @if ($workshop->hasLogo())
                    <img class="report-workshop__logo" src="{{ route('app.settings.logo') }}" alt="" width="72" height="72">
                @endif
                <div>
                    <p class="report-workshop__name">{{ $workshop->name }}</p>
                    @if ($workshop->organization_number)
                        <p class="report-workshop__contact">Org.nr {{ $workshop->organization_number }}</p>
                    @endif
                    @if ($workshop->contact_email)
                        <p class="report-workshop__contact">{{ $workshop->contact_email }}</p>
                    @endif
                    @if (is_string($workshop->phone) && $workshop->phone !== '')
                        <p class="report-workshop__contact">@include('partials.workshop.phone-link', ['phone' => \App\Support\SwedishPhone::format($workshop->phone), 'href' => \App\Support\SwedishPhone::telHref($workshop->phone)])</p>
                    @endif
                    @if ($workshop->address)
                        <p class="report-workshop__contact">{{ $workshop->address }}</p>
                    @endif
                </div>
            </div>
            <div class="report-header__actions no-print">
                <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Till projektet</a>
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.intake.pdf', $project) }}">Ladda ner PDF</a>
                <button class="workshop-button workshop-button--primary" type="button" data-print-report>Skriv ut</button>
            </div>
        </header>

        <section class="report-project" aria-labelledby="page-title">
            <div class="report-project__title-row">
                <div>
                    <p class="workshop-eyebrow">MOTTAGNINGSPROTOKOLL</p>
                    <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
                </div>
            </div>
            <dl class="report-facts">
                @if ($project->customerLabel())
                    <div><dt>Kund</dt><dd>{{ $project->customerLabel() }}</dd></div>
                @endif
                @if ($project->location)
                    <div><dt>Plats</dt><dd>{{ $project->location }}</dd></div>
                @endif
                @if ($project->created_at)
                    <div><dt>Mottaget</dt><dd>{{ \App\Support\SwedishDate::format($project->created_at->copy()->timezone('Europe/Stockholm')) }}</dd></div>
                @endif
                <div><dt>Antal bågar</dt><dd>{{ $sashes->count() }}</dd></div>
            </dl>
            <p>Protokollet beskriver bågarnas skick när de togs emot av verkstaden.</p>
        </section>

        @if ($sashes->isEmpty())
            <section class="report-empty" aria-label="Bågar">
                <h2>Inga bågar i projektet ännu</h2>
                <p>När bågar har lagts till visas deras skick vid mottagning här.</p>
            </section>
        @else
            <section class="report-sashes" aria-labelledby="intake-sashes-title">
                <h2 class="workshop-section-heading" id="intake-sashes-title">Bågar</h2>
                @foreach ($sashes as $sash)
                    @php
                        $beforePhotos = $sash->photos->filter(fn ($photo): bool => $photo->isBefore());
                    @endphp
                    <article class="report-sash">
                        <header class="report-sash__heading">
                            <div>
                                <p class="workshop-eyebrow">BÅGE</p>
                                <h3>{{ $sash->code }}</h3>
                                <p class="report-sash__location">{{ collect([$sash->room, $sash->location])->filter()->join(' · ') ?: 'Plats inte angiven' }}</p>
                            </div>
                        </header>
                        <div class="report-sash__notes">
                            <h4>Skick vid mottagning</h4>
                            <p>{{ is_string($sash->intake_notes) && trim($sash->intake_notes) !== '' ? $sash->intake_notes : 'Ingen anmärkning' }}</p>
                        </div>
                        @if ($beforePhotos->isNotEmpty())
                            <div class="report-photo-grid" aria-label="Bilder före för båge {{ $sash->code }}">
                                @foreach ($beforePhotos as $photo)
                                    <figure>
                                        <img src="{{ route('app.sashes.photos.show', [$project, $sash, $photo]) }}" alt="Före för båge {{ $sash->code }}" width="640" height="480" loading="lazy" decoding="async">
                                        <figcaption>{{ $photo->documentCaption() }}</figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif

        <section class="delivery-approval" aria-labelledby="intake-approval-title">
            <h2 id="intake-approval-title">Godkännande</h2>
            @if ($project->intakeIsApproved())
                <p>{{ $project->intakeApprovalLabel() }}</p>
            @else
                <p>Inte godkänt av kunden.</p>
            @endif
        </section>

        <footer class="report-footer">
            <span class="report-footer__product">Bågspår</span>
            <span>{{ \App\Support\SwedishDate::format(now()->timezone('Europe/Stockholm')) }}</span>
        </footer>
    </div>
@endsection
