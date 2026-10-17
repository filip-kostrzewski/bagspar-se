@extends('layouts.workshop')

@section('title', 'Rapport: '.$project->name.' — Bågspår')
@section('body_class', 'workshop-print-report')

@section('content')
    @php
        $sashCount = $sashes->count();
    @endphp
    <div class="workspace-page report-page">
        <nav class="workshop-breadcrumbs no-print" aria-label="Brödsmulor">
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Rapport</span>
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
                    @if ($workshop->address)
                        <p class="report-workshop__contact">{{ $workshop->address }}</p>
                    @endif
                </div>
            </div>
            <div class="report-header__actions no-print">
                <form class="report-notes-toggle" method="GET" action="{{ route('app.projects.report', $project) }}">
                    <label class="workshop-checkbox" for="include-work-notes">
                        <input id="include-work-notes" name="anteckningar" type="checkbox" value="1" @checked($includeWorkNotes)>
                        <span>Visa arbetsanteckningar i rapporten</span>
                    </label>
                    <label class="workshop-checkbox" for="include-work-time">
                        <input id="include-work-time" name="arbetstid" type="checkbox" value="1" @checked($includeWorkTime)>
                        <span>Visa arbetstid i rapporten</span>
                    </label>
                    <button class="workshop-button workshop-button--quiet" type="submit">Uppdatera rapporten</button>
                </form>
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
            </div>
            <dl class="report-facts">
                @if ($project->customerLabel())
                    <div><dt>Kund</dt><dd>{{ $project->customerLabel() }}</dd></div>
                @endif
                @if ($project->customer?->contact_name)
                    <div><dt>Kontakt</dt><dd>{{ $project->customer->contact_name }}</dd></div>
                @endif
                @if ($project->customer?->phone)
                    <div><dt>Telefon</dt><dd>@include('partials.workshop.phone-link', ['phone' => $project->customer->phone, 'href' => $project->customer->phoneHref()])</dd></div>
                @endif
                @if ($project->customer?->email)
                    <div><dt>E-post</dt><dd>{{ $project->customer->email }}</dd></div>
                @endif
                @if ($project->customer?->address)
                    <div><dt>Kundadress</dt><dd>{{ $project->customer->address }}</dd></div>
                @endif
                @if ($project->location)
                    <div><dt>Plats</dt><dd>{{ $project->location }}</dd></div>
                @endif
                @if ($project->delivery_date)
                    <div><dt>Beräknad leverans</dt><dd>{{ \App\Support\SwedishDate::format($project->delivery_date) }}</dd></div>
                @endif
                @if ($includeWorkTime)
                    <div><dt>Arbetstid totalt</dt><dd>{{ \App\Support\WorkDuration::label($timeTotalMinutes) }}</dd></div>
                    @foreach ($timeByPerson as $name => $minutes)
                        <div><dt>{{ $name }}</dt><dd>{{ \App\Support\WorkDuration::label($minutes) }}</dd></div>
                    @endforeach
                @endif
                <div><dt>Antal bågar</dt><dd>{{ $sashCount }}</dd></div>
                @if ($project->created_at)
                    <div><dt>Skapat</dt><dd>{{ \App\Support\SwedishDate::formatDateTime($project->created_at) }}</dd></div>
                @endif
            </dl>
            @if ($includeWorkNotes && $project->notes)
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
                            <p class="report-sash__state">@include('partials.workshop.stage-label', ['stage' => $sash->stage])</p>
                        </header>
                        @if ($includeWorkTime)
                            @php
                                $sashTime = $timeEntries->where('sash_id', $sash->getKey());
                                $sashTimeByPerson = $sashTime->groupBy('user_name')->map(fn ($rows): int => (int) $rows->sum('minutes'))->sortKeys();
                            @endphp
                            <div class="report-sash__time">
                                <h4>Arbetstid</h4>
                                <p>Totalt {{ \App\Support\WorkDuration::label((int) $sashTime->sum('minutes')) }}</p>
                                @if ($sashTimeByPerson->isNotEmpty())
                                    <ul>
                                        @foreach ($sashTimeByPerson as $name => $minutes)
                                            <li>{{ $name }}: {{ \App\Support\WorkDuration::label($minutes) }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif
                        @if ($includeWorkNotes && $sash->notes)
                            <div class="report-sash__notes">
                                <h4>Arbetsanteckningar</h4>
                                <p>{{ $sash->notes }}</p>
                            </div>
                        @endif
                        @if ($sash->photos->isNotEmpty())
                            <div class="report-photo-grid" aria-label="Bilder för båge {{ $sash->code }}">
                                @foreach ($sash->photos as $photo)
                                    <figure>
                                        <img src="{{ route('app.sashes.photos.show', [$project, $sash, $photo]) }}" alt="{{ $photo->documentMomentLabel() ?? 'Foto' }} för båge {{ $sash->code }}" width="640" height="480" loading="eager" decoding="sync">
                                        <figcaption>{{ $photo->documentCaption() }}</figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif

        @if (is_string($clientStatusUrl) && is_string($clientStatusQr) && $clientStatusQr !== '')
            <section class="report-client-status" aria-labelledby="client-status-title">
                <h2 id="client-status-title">Status för kunden</h2>
                <p>Skanna koden för att se etapperna och bilderna före och efter. Anteckningar ingår inte.</p>
                <div class="report-client-status__qr">{!! $clientStatusQr !!}</div>
                <p class="report-client-status__url">{{ $clientStatusUrl }}</p>
            </section>
        @endif

        <footer class="report-footer">
            <span class="report-footer__product">Bågspår</span>
            <span>{{ \App\Support\SwedishDate::format(now()->timezone('Europe/Stockholm')) }}</span>
        </footer>
    </div>
@endsection
