@extends('layouts.client-status')

@section('title', 'Offert: '.$project->name.' — '.$workshop->name)

@section('content')
    <article class="client-status quote-public">
        <header class="report-header">
            <div class="report-workshop">
                @if ($workshop->hasLogo())
                    <img class="report-workshop__logo" src="{{ route('quote.logo', ['token' => $token]) }}" alt="" width="72" height="72">
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
            <p class="workshop-eyebrow">OFFERT</p>
            <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
            @if ($project->customerLabel())
                <p class="client-status__location">{{ $project->customerLabel() }}</p>
            @endif
            @if ($project->location)
                <p class="client-status__location">{{ $project->location }}</p>
            @endif
            @if (is_string($quote->introduction) && $quote->introduction !== '')
                <p class="quote-intro">{{ $quote->introduction }}</p>
            @endif
        </section>

        <section class="quote-public__lines" aria-label="Offertens rader">
            @include('partials.workshop.quote-lines', ['quote' => $quote])
            @if ($quote->valid_until)
                <p>Offerten gäller till {{ \App\Support\SwedishDate::format($quote->valid_until) }}.</p>
            @endif
            <aside class="basis-notice" role="note">
                <p>Priset gäller före eventuellt ROT-avdrag, som görs på fakturan enligt Skatteverkets regler.</p>
            </aside>
        </section>

        <section class="delivery-approval" aria-labelledby="quote-accept-title">
            <h2 id="quote-accept-title">Godkänn offert</h2>
            @if ($quote->isAccepted())
                <p>Offerten godkändes {{ \App\Support\SwedishDate::formatDateTime($quote->accepted_at) }} av {{ $quote->accepted_name }}.</p>
            @else
                <p>När du godkänner offerten lämnar du ditt namn. Namnet och tidpunkten sparas på offerten.</p>
                @if ($errors->any())
                    <div class="workshop-error-summary" role="alert">
                        <ul>
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="workshop-form" method="POST" action="{{ route('quote.accept', ['token' => $token]) }}" novalidate>
                    @csrf
                    <div class="form-field">
                        <label for="quote-accept-name">Ditt namn</label>
                        <input id="quote-accept-name" name="name" type="text" maxlength="150" required value="{{ is_string(old('name')) ? old('name') : '' }}" @if ($errors->has('name')) aria-invalid="true" aria-describedby="quote-accept-name-error" @endif>
                        @error('name')
                            <p class="form-field__error" id="quote-accept-name-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button class="workshop-button workshop-button--primary" type="submit">Godkänn offert</button>
                </form>
            @endif
        </section>

        <footer class="report-footer">
            <span class="report-footer__product">Bågspår</span>
        </footer>
    </article>
@endsection
