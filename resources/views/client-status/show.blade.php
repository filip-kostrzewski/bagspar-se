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
                    @if (is_string($workshop->phone) && $workshop->phone !== '')
                        <p class="report-workshop__contact">@include('partials.workshop.phone-link', ['phone' => \App\Support\SwedishPhone::format($workshop->phone), 'href' => \App\Support\SwedishPhone::telHref($workshop->phone)])</p>
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
            @if ($project->delivery_date)
                <p class="client-status__location">Beräknad leverans {{ \App\Support\SwedishDate::format($project->delivery_date) }}</p>
            @endif
        </section>

        @php
            $hasIntakeProtocol = $sashes->contains(fn ($sash): bool => (is_string($sash->intake_notes) && trim($sash->intake_notes) !== '')
                || $sash->photos->contains(fn ($photo): bool => $photo->isBefore()));
            $intakeErrors = $errors->getBag('intake');
        @endphp
        @if ($hasIntakeProtocol)
            <section class="delivery-approval intake-protocol" aria-labelledby="intake-title">
                <h2 id="intake-title">Mottagningsprotokoll</h2>
                <p>Så här såg bågarna ut när verkstaden tog emot dem. Bilderna före finns under varje båge längre ned.</p>
                <ul class="intake-protocol__list">
                    @foreach ($sashes as $sash)
                        <li>
                            <strong>{{ $sash->code }}</strong>
                            <span>{{ collect([$sash->room, $sash->location])->filter()->join(' · ') ?: 'Plats inte angiven' }}</span>
                            <p>{{ is_string($sash->intake_notes) && trim($sash->intake_notes) !== '' ? $sash->intake_notes : 'Ingen anmärkning' }}</p>
                        </li>
                    @endforeach
                </ul>
                @if ($project->intakeIsApproved())
                    <p>Mottagningsprotokollet godkändes {{ \App\Support\SwedishDate::formatDateTime($project->intake_approved_at) }}@if (is_string($project->intake_approved_name) && $project->intake_approved_name !== '') av {{ $project->intake_approved_name }}@endif.</p>
                @else
                    @if ($intakeErrors->any())
                        <div class="workshop-error-summary" role="alert">
                            <ul>
                                @foreach ($intakeErrors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="workshop-form" method="POST" action="{{ route('client-status.intake-approve', ['token' => $token]) }}" novalidate>
                        @csrf
                        <div class="form-field">
                            <label for="intake-approval-name">Godkänn mottagningsprotokollet</label>
                            <input id="intake-approval-name" name="name" type="text" maxlength="150" required placeholder="Ditt namn" value="{{ $intakeErrors->any() && is_string(old('name')) ? old('name') : '' }}" @if ($intakeErrors->has('name')) aria-invalid="true" aria-describedby="intake-approval-name-error" @endif>
                            @if ($intakeErrors->has('name'))
                                <p class="form-field__error" id="intake-approval-name-error">{{ $intakeErrors->first('name') }}</p>
                            @endif
                        </div>
                        <button class="workshop-button workshop-button--primary" type="submit">Godkänn mottagningsprotokollet</button>
                    </form>
                @endif
            </section>
        @endif

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

        <section class="delivery-approval" aria-labelledby="approval-title">
            <h2 id="approval-title">Godkänn leverans</h2>
            @if ($project->delivery_approved_at)
                <p>Leveransen godkändes {{ \App\Support\SwedishDate::formatDateTime($project->delivery_approved_at) }}@if (is_string($project->delivery_approved_name) && $project->delivery_approved_name !== '') av {{ $project->delivery_approved_name }}@endif. Tidpunkten står på verkstadens projektrapport.</p>
            @elseif (! $sashes->isNotEmpty() || ! $sashes->every(fn ($sash): bool => $sash->stage === 'ready'))
                <p>Du kan godkänna leveransen när alla bågar är klara.</p>
            @else
                <p>När du tar emot arbetet kan du lämna ditt namn. Tidpunkten sparas på verkstadens projektrapport.</p>
                @if ($errors->any())
                    <div class="workshop-error-summary" role="alert">
                        <ul>
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="workshop-form" method="POST" action="{{ route('client-status.approve', ['token' => $token]) }}" novalidate>
                    @csrf
                    <div class="form-field">
                        <label for="approval-name">Ditt namn</label>
                        <input id="approval-name" name="name" type="text" maxlength="120" required value="{{ is_string(old('name')) ? old('name') : '' }}" @if ($errors->has('name')) aria-invalid="true" aria-describedby="approval-name-error" @endif>
                        @error('name')
                            <p class="form-field__error" id="approval-name-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="workshop-checkbox" for="approval-confirm">
                        <input id="approval-confirm" name="confirm" type="checkbox" value="1" @checked(old('confirm')) @if ($errors->has('confirm')) aria-invalid="true" aria-describedby="approval-confirm-error" @endif>
                        <span>Jag godkänner leveransen</span>
                    </label>
                    @error('confirm')
                        <p class="form-field__error" id="approval-confirm-error">{{ $message }}</p>
                    @enderror
                    <button class="workshop-button workshop-button--primary" type="submit">Godkänn leverans</button>
                </form>
            @endif
        </section>

        <footer class="report-footer">
            <span class="report-footer__product">Bågspår</span>
        </footer>
    </article>
@endsection
