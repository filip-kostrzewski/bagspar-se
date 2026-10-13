@extends('layouts.app')

@section('title', 'Bågspår — arbetskort för fönsterrenovering')
@section('meta_description', 'Ett planerat verktyg för fönsterverkstäder: märkning, bilder, arbetssteg och dokumentation på ett ställe. Anmäl intresse för Bågspårs pilotprojekt.')

@section('content')
<main id="main">
    <section class="hero page-shell" aria-labelledby="hero-title">
        <div class="hero__copy">
            <p class="eyebrow">FÖR VERKSTÄDER SOM RENOVERAR FÖNSTER</p>
            <h1 id="hero-title" class="display-heading">Ordning på varje båge.</h1>
            <p class="hero__intro">Vi utvecklar ett enkelt verktyg som samlar märkning, bilder och arbetssteg — från första fotot till återmontering.</p>
            <div class="hero__actions">
                <a class="button" href="#intresse">Jag vill testa Bågspår</a>
                <a class="text-link" href="#arbetsflode">Se hur idén fungerar</a>
            </div>
            <p class="hero__disclosure">Planerat pilotpris: 790 kr/mån exkl. moms. Intresseanmälan är inte bindande.</p>
        </div>

        <figure class="hero-visual">
            <img class="hero-visual__photo" src="{{ asset('images/workshop-hero.webp') }}" alt="Äldre träbåge med små glasrutor på en verkstadsbänk" width="1122" height="1402" fetchpriority="high">
            <span class="brass-tag" aria-label="Märkning B-04">B-04</span>

            <div class="work-card" aria-label="Illustrativt arbetskort för båge B-04">
                <p class="work-card__caption">Exempel på arbetskort</p>
                <div class="work-card__heading">
                    <div>
                        <h2>Båge B-04</h2>
                        <p>Våning 2 · Kök</p>
                    </div>
                    <span class="work-card__status">Redo för målning</span>
                </div>
                <ul class="work-card__steps">
                    <li><span class="step-check" aria-hidden="true">✓</span>Märkt och fotograferad</li>
                    <li><span class="step-check" aria-hidden="true">✓</span>Trä lagat</li>
                    <li><span class="step-check" aria-hidden="true">✓</span>Glas och kitt klart</li>
                </ul>
            </div>
            <figcaption class="hero-visual__caption">Från verkstaden till rätt fönsteröppning.</figcaption>
        </figure>
    </section>

    <section class="proposition section-space page-shell" aria-labelledby="proposition-title">
        <div class="proposition__intro">
            <p class="eyebrow">EN PLATS FÖR HELA ARBETET</p>
            <h2 id="proposition-title" class="section-heading">Låt hantverket ta plats. Inte letandet.</h2>
            <p class="body-copy">När flera projekt pågår samtidigt behöver varje båge, bild och anteckning höra ihop. Bågspår är tänkt att ge verkstaden en enkel överblick — och ett tydligt underlag när arbetet är klart.</p>
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
                <p>Samla före- och efterbilder tillsammans med arbetsanteckningarna.</p>
            </article>
            <article class="proposition-item">
                <span class="proposition-item__number">03</span>
                <h3>Tydligt vad som återstår</h3>
                <p>Följ arbetsstegen och se vad som är redo för nästa moment.</p>
            </article>
        </div>
    </section>

    <section id="arbetsflode" class="workflow" aria-labelledby="workflow-title">
        <div class="workflow__inner page-shell">
            <div class="workflow__intro">
                <p class="eyebrow eyebrow--light">SÅ FUNGERAR IDÉN</p>
                <h2 id="workflow-title" class="section-heading section-heading--light">En båge. Ett arbetskort. Hela vägen.</h2>
                <p>Det här är arbetsflödet vi vill utveckla tillsammans med de första verkstäderna.</p>
            </div>
            <ol class="workflow-list">
                <li class="workflow-step">
                    <span class="workflow-step__number">01</span>
                    <div>
                        <h3>Märk och fotografera</h3>
                        <p>Skapa ett projekt och ge varje båge ett nummer. Lägg till bilder, plats och det som behöver göras.</p>
                    </div>
                </li>
                <li class="workflow-step">
                    <span class="workflow-step__number">02</span>
                    <div>
                        <h3>Följ arbetet i mobilen</h3>
                        <p>Öppna arbetskortet via en QR-kod och uppdatera steget när arbetet går vidare.</p>
                    </div>
                </li>
                <li class="workflow-step">
                    <span class="workflow-step__number">03</span>
                    <div>
                        <h3>Samla dokumentationen</h3>
                        <p>Tanken är att bilder och utförda moment ska kunna sammanställas till ett enkelt PDF-underlag.</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section id="pilot" class="pilot section-space page-shell" aria-labelledby="pilot-title">
        <div class="pilot__copy">
            <p class="eyebrow">VAR MED FRÅN BÖRJAN</p>
            <h2 id="pilot-title" class="section-heading">Byggt tillsammans med verkstäder som din.</h2>
            <p class="body-copy">Vi söker verkstäder som vill påverka den första versionen. Berätta hur ni arbetar i dag och vad som brukar vara svårt att hålla ordning på.</p>
            <p class="pilot__disclosure">Bågspår är under utveckling. Vi tar emot intresseanmälningar inför ett framtida pilotprojekt. Du betalar ingenting genom att anmäla intresse.</p>
        </div>
        <div class="pilot-price" aria-label="Planerat pris: 790 kronor per månad exklusive moms">
            <p class="pilot-price__label">Planerat pris per verkstad</p>
            <p class="pilot-price__amount"><span>790</span><span class="pilot-price__unit">kr/mån</span></p>
            <p class="pilot-price__vat">exkl. moms</p>
        </div>
    </section>

    <section id="intresse" class="interest" aria-labelledby="interest-title">
        <div class="interest__inner page-shell">
            <div class="interest__intro">
                <p class="eyebrow">PILOTPROJEKTET</p>
                <h2 id="interest-title" class="section-heading">Vill du få bättre ordning i verkstaden?</h2>
                <p>Lämna din e-postadress och berätta gärna lite om ert arbetssätt. Vi hör av oss personligen om pilotprojektet.</p>
                <p class="interest__privacy-note">Uppgifterna används för att hantera din intresseanmälan. Läs mer i <a href="{{ route('privacy') }}">integritetspolicyn</a>.</p>
            </div>

            <div class="interest__form-wrap">
                @if (session('pilot_lead_received'))
                    <div id="form-result" class="form-result" role="status" aria-live="polite" tabindex="-1">
                        <h3>Tack! Din intresseanmälan är mottagen.</h3>
                        <p>Vi har sparat dina uppgifter och återkommer via e-post om pilotprojektet.</p>
                        <a class="text-link" href="{{ route('home') }}">Tillbaka till Bågspår</a>
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

                        <div class="form-field">
                            <label for="message">Vad vill ni få bättre ordning på?</label>
                            <textarea id="message" name="message" rows="4" maxlength="2000" placeholder="Till exempel märkning, bilder eller dokumentation." @if ($errors->has('message')) aria-invalid="true" aria-describedby="message-error" @endif>{{ old('message') }}</textarea>
                            @error('message')<p id="message-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-field form-field--consent">
                            <input id="contact_consent" name="contact_consent" type="checkbox" value="yes" required @checked(old('contact_consent')) @if ($errors->has('contact_consent')) aria-invalid="true" aria-describedby="consent-error" @endif>
                            <label for="contact_consent">Jag godkänner att ni kontaktar mig om Bågspår och har läst <a href="{{ route('privacy') }}">integritetspolicyn</a>. <span aria-hidden="true">*</span></label>
                            @error('contact_consent')<p id="consent-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                        </div>

                        <button class="button interest-form__submit" type="submit">Anmäl intresse</button>
                        <p class="interest-form__note">Ingen betalning. Inget bindande avtal.</p>
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
                <p>Inte ännu. Vi tar emot intresseanmälningar och vill förstå verkstädernas behov innan vi bygger den första versionen.</p>
            </details>
            <details>
                <summary>Behöver vi byta vårt nuvarande ekonomisystem?</summary>
                <p>Idén är ett separat verktyg för bågar och arbetsdokumentation. Vi planerar inte att ersätta ert ekonomisystem.</p>
            </details>
            <details>
                <summary>Kommer det att fungera på telefonen?</summary>
                <p>Den planerade första versionen ska kunna användas i mobilens webbläsare.</p>
            </details>
            <details>
                <summary>Vad händer när jag anmäler intresse?</summary>
                <p>Vi kontaktar dig via e-post för att förstå ert arbetssätt och berätta mer om ett möjligt pilotprojekt. Anmälan är inte bindande.</p>
            </details>
        </div>
    </section>
</main>
@endsection
