@extends('layouts.app')

@section('title', 'Bågspår — arbetskort för fönsterrenovering')
@section('meta_description', 'Arbetskort för fönsterverkstäder. Märkning, bilder, anteckningar och arbetssteg på varje båge, även när täckningen är svag.')

@section('content')
<main id="main">
    <section class="hero page-shell" aria-labelledby="hero-title">
        <div class="hero__copy">
            <p class="eyebrow">FÖR VERKSTÄDER SOM RENOVERAR FÖNSTER</p>
            <h1 id="hero-title" class="display-heading">Ordning på varje båge.</h1>
            <p class="hero__intro">Varje båge får ett arbetskort med märkning, bilder, anteckningar och arbetssteg, från inlämning till återmontering.</p>
            <div class="hero__actions">
                <a class="button" href="#intresse">Anmäl verkstaden</a>
                <a class="text-link" href="{{ route('demo') }}">Se hur ett arbetskort ser ut</a>
            </div>
            <p class="hero__disclosure">{{ config('bagspar.pilot_offer') }}<br>{{ config('bagspar.pilot_price_line') }}</p>
        </div>

        <figure class="hero-visual">
            <img class="hero-visual__photo" src="{{ asset('images/workshop-hero.webp') }}" alt="Äldre träbåge med små glasrutor på en verkstadsbänk" width="1122" height="1402" fetchpriority="high">
            <span class="brass-tag" aria-label="Märkning B-04">B-04</span>

            <div class="work-card" aria-label="Exempel på arbetskort för båge B-04">
                <p class="work-card__caption">Exempel på arbetskort</p>
                <div class="work-card__heading">
                    <div>
                        <h2>Båge B-04</h2>
                        <p>Våning 2 · Kök</p>
                    </div>
                    <span class="work-card__status">Kittning</span>
                </div>
                <ul class="work-card__steps">
                    <li><span class="step-check" aria-hidden="true">✓</span>Inlämnad</li>
                    <li><span class="step-check" aria-hidden="true">✓</span>Skrapning</li>
                    <li><span class="step-current" aria-hidden="true"></span>Kittning</li>
                </ul>
            </div>
            <figcaption class="hero-visual__caption">Från verkstaden till rätt fönsteröppning.</figcaption>
        </figure>
    </section>

    <section class="proposition section-space page-shell" aria-labelledby="proposition-title">
        <div class="proposition__intro">
            <p class="eyebrow">EN PLATS FÖR HELA ARBETET</p>
            <h2 id="proposition-title" class="section-heading">Låt hantverket ta plats. Inte letandet.</h2>
            <p class="body-copy">När flera projekt pågår samtidigt behöver varje båge, bild och anteckning höra ihop. Bågspår samlar det på ett arbetskort, så ni ser vad som är gjort och vad som återstår.</p>
            <p class="body-copy">Bågspår är byggt offline-först: arbetskortet går att läsa och uppdatera när täckningen är svag, och ändringarna synkas när anslutningen kommer tillbaka.</p>
        </div>
        <div class="proposition__items">
            <article class="proposition-item">
                <span class="proposition-item__number">01</span>
                <h3>Rätt båge, rätt plats</h3>
                <p>Koppla varje båge till projekt, rum och fönsteröppning.</p>
            </article>
            <article class="proposition-item">
                <span class="proposition-item__number">02</span>
                <h3>Bilder där de hör hemma</h3>
                <p>Före- och efterbilder ligger tillsammans med anteckningarna.</p>
            </article>
            <article class="proposition-item">
                <span class="proposition-item__number">03</span>
                <h3>Tydligt vad som återstår</h3>
                <p>Stegen visar var bågen är: inlämnad, skrapning, kittning, målning eller klar.</p>
            </article>
            <article class="proposition-item">
                <span class="proposition-item__number">04</span>
                <h3>Fungerar i svag täckning</h3>
                <p>Kortet ligger kvar i mobilen. Steg och anteckningar sparas där och synkas när anslutningen kommer tillbaka.</p>
            </article>
        </div>
    </section>

    <section id="arbetsflode" class="workflow" aria-labelledby="workflow-title">
        <div class="workflow__inner page-shell">
            <div class="workflow__intro">
                <p class="eyebrow eyebrow--light">SÅ FUNGERAR DET</p>
                <h2 id="workflow-title" class="section-heading section-heading--light">En båge. Ett arbetskort. Hela vägen.</h2>
                <p>Ett kort per båge. Öppna det i mobilen, flytta steget och samla bilder och anteckningar på samma ställe.</p>
                <p><a class="text-link" href="{{ route('demo') }}">Se hur ett arbetskort ser ut</a></p>
                <nav class="workflow__guides" aria-label="Guider">
                    <a href="{{ route('guides.documentation') }}">Dokumentation</a>
                    <a href="{{ route('guides.work-card') }}">Arbetskort</a>
                    <a href="{{ route('guides.marking') }}">Märkning</a>
                </nav>
            </div>
            <ol class="workflow-list">
                <li class="workflow-step">
                    <span class="workflow-step__number">01</span>
                    <div>
                        <h3>Märk och fotografera</h3>
                        <p>Skapa ett projekt och ge varje båge ett nummer. Lägg till bilder, plats och det som ska göras.</p>
                    </div>
                </li>
                <li class="workflow-step">
                    <span class="workflow-step__number">02</span>
                    <div>
                        <h3>Följ arbetet i mobilen</h3>
                        <p>Öppna arbetskortet via QR-koden på bågen och flytta steget när arbetet går vidare.</p>
                    </div>
                </li>
                <li class="workflow-step">
                    <span class="workflow-step__number">03</span>
                    <div>
                        <h3>Anteckna på kortet</h3>
                        <p>Skriv en anteckning där arbetet sker. Bilderna och notiserna följer med bågen till rätt öppning.</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section id="pilot" class="pilot section-space page-shell" aria-labelledby="pilot-title">
        <div class="pilot__copy">
            <p class="eyebrow">PILOTEN</p>
            @if (filled(config('bagspar.pilot_offer')))
                <h2 id="pilot-title" class="section-heading">{{ config('bagspar.pilot_offer') }}</h2>
            @else
                <h2 id="pilot-title" class="section-heading">Piloten</h2>
            @endif
            <p class="body-copy">Varje båge får ett arbetskort i mobilen, med steg, bilder och anteckningar.</p>
            <p class="pilot__disclosure">{{ config('bagspar.pilot_price_line') }}</p>
            <p class="pilot__disclosure">{{ config('bagspar.pilot_start_line') }}</p>
        </div>
        <div class="pilot-price" aria-label="790 kronor per månad exklusive moms efter piloten">
            <p class="pilot-price__label">Efter piloten</p>
            <p class="pilot-price__amount"><span>790</span><span class="pilot-price__unit">kr/mån</span></p>
            <p class="pilot-price__vat">exkl. moms</p>
        </div>
    </section>

    <section id="intresse" class="interest" aria-labelledby="interest-title">
        <div class="interest__inner page-shell">
            <div class="interest__intro">
                <p class="eyebrow">ANMÄL VERKSTADEN</p>
                <h2 id="interest-title" class="section-heading">Anmäl verkstaden.</h2>
                <p>E-post och verkstadens namn räcker. Du kommer direkt till exempelarbetskortet.</p>
                @include('partials.pilot-offer')
                <p class="interest__privacy-note">Vi sparar verkstadens namn, din e-postadress och tidpunkten för anmälan. Läs mer i <a href="{{ route('privacy') }}">integritetspolicyn</a>.</p>
            </div>

            <div class="interest__form-wrap">
                @if (session('pilot_lead_received'))
                    <div id="form-result" class="form-result" role="status" aria-live="polite" tabindex="-1">
                        <h3>Tack, verkstaden är anmäld.</h3>
                        <p>Vi har sparat namn och e-post. Öppna exempelarbetskortet och se hur en båge följs genom verkstaden.</p>
                        <a class="button" href="{{ route('demo') }}">Se hur ett arbetskort ser ut</a>
                    </div>
                @else
                    <form class="interest-form" action="{{ route('pilot-leads.store') }}" method="POST" data-interest-form>
                        @csrf

                        <div class="form-honeypot" aria-hidden="true">
                            <label for="website">Lämna det här fältet tomt</label>
                            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-field">
                            <label for="company">Företag / verkstad <span aria-hidden="true">*</span></label>
                            <input id="company" name="company" type="text" autocomplete="organization" maxlength="160" required value="{{ old('company') }}" @if ($errors->has('company')) aria-invalid="true" aria-describedby="company-error" @endif>
                            @error('company')<p id="company-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-field">
                            <label for="email">E-post <span aria-hidden="true">*</span></label>
                            <input id="email" name="email" type="email" autocomplete="email" maxlength="254" required value="{{ old('email') }}" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                            @error('email')<p id="email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <button class="button interest-form__submit" type="submit">Anmäl verkstaden</button>
                        <p class="interest-form__note">Ingen betalning nu. Ingen bindningstid. <a href="{{ route('privacy') }}">Integritetspolicy</a></p>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="faq section-space page-shell" aria-labelledby="faq-title">
        <div class="faq__intro">
            <p class="eyebrow">BRA ATT VETA</p>
            <h2 id="faq-title" class="section-heading">Några saker att veta</h2>
        </div>
        <div class="faq-list">
            <details>
                <summary>Kan vi använda Bågspår redan nu?</summary>
                <p>Ja. Anmäl verkstaden så öppnar du exempelarbetskortet direkt. Piloten startar när tre verkstäder anmält sig.</p>
            </details>
            <details>
                <summary>Behöver vi byta vårt nuvarande ekonomisystem?</summary>
                <p>Bågspår håller ordning på bågar och arbetsdokumentation. Ekonomisystemet är kvar.</p>
            </details>
            <details>
                <summary>Fungerar det på telefonen?</summary>
                <p>Ja. Arbetskortet öppnas i mobilens webbläsare och går att läsa och uppdatera när täckningen är svag.</p>
            </details>
            <details>
                <summary>Vad händer när jag anmäler verkstaden?</summary>
                <p>Vi sparar verkstadens namn och din e-postadress. Sedan öppnar du exempelarbetskortet. Anmälan innebär ingen betalning.</p>
            </details>
        </div>
    </section>
</main>
@endsection
