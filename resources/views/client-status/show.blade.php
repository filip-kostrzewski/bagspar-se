@extends('layouts.client-status')

@section('title', $project->name.' — '.$workshop->name)

@section('content')
    <article class="client-status">
        <header class="report-header">
            <div class="report-workshop">
                @if ($workshop->hasLogo())
                    <img class="report-workshop__logo" src="{{ route('client-status.logo', ['token' => $token]) }}" alt="" width="72" height="72">
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
        </header>

        <section class="report-project" aria-labelledby="page-title">
            <p class="workshop-eyebrow">STATUS</p>
            <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
            @if ($project->customerLabel())
                <p class="client-status__location">{{ $project->customerLabel() }}</p>
            @endif
            @if ($project->customer?->contact_name || $project->customer?->email || $project->customer?->address)
                <p class="client-status__location">{{ collect([$project->customer?->contact_name, $project->customer?->email, $project->customer?->address])->filter()->join(' · ') }}</p>
            @endif
            @if ($project->location)
                <p class="client-status__location">{{ $project->location }}</p>
            @endif
        </section>

        @if ($sashes->isEmpty())
            <p>Inga bågar är inlagda i projektet ännu.</p>
        @else
            <section class="report-sashes" aria-label="Bågar">
                @foreach ($sashes as $sash)
                    <article class="report-sash">
                        <header class="report-sash__heading">
                            <div>
                                <p class="workshop-eyebrow">BÅGE</p>
                                <h2>{{ $sash->code }}</h2>
                                <p class="report-sash__location">{{ collect([$sash->room, $sash->location])->filter()->join(' · ') ?: 'Plats inte angiven' }}</p>
                            </div>
                            <p class="report-sash__state">@include('partials.workshop.stage-label', ['stage' => $sash->stage])</p>
                        </header>
                        @if ($sash->photos->isNotEmpty())
                            <div class="report-photo-grid" aria-label="Bilder för båge {{ $sash->code }}">
                                @foreach ($sash->photos as $photo)
                                    @php
                                        $photoUrl = route('client-status.photos', ['token' => $token, 'photo' => $photo]);
                                        $photoAlt = ($photo->documentMomentLabel() ?? 'Foto').' för båge '.$sash->code;
                                    @endphp
                                    <figure>
                                        <a class="status-photo" href="{{ $photoUrl }}" data-enlarge>
                                            <img src="{{ $photoUrl }}" alt="{{ $photoAlt }}" width="640" height="480" loading="lazy" decoding="async">
                                        </a>
                                        <figcaption>{{ $photo->documentCaption() }}</figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif

        <footer class="report-footer">
            <span class="report-footer__product">Bågspår</span>
        </footer>
    </article>
@endsection
