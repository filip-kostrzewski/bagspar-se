@extends('layouts.app')

@section('title', 'Personuppgiftsbiträdesavtal — Bågspår')
@section('meta_description', 'Personuppgiftsbiträdesavtal för Bågspår: så behandlar Prodi Digital AB verkstadens kunduppgifter för verkstadens räkning enligt artikel 28 i dataskyddsförordningen.')

@section('content')
<main id="main" class="privacy-page page-shell">
    <a class="text-link privacy-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">AVTAL</p>
    <h1 class="display-heading">Personuppgiftsbiträdesavtal</h1>
    <div class="privacy-page__body body-copy">
        <p>Det här avtalet gäller när en verkstad lägger sina kunders uppgifter i Bågspår. Det följer av artikel 28 i dataskyddsförordningen (GDPR).</p>
        <p class="privacy-page__updated">Uppdaterad 17 oktober 2026 · Version 1.0.</p>

        <h2>1. Parter och bakgrund</h2>
        <p>Verkstaden som skapar kontot är personuppgiftsansvarig. Verkstaden bestämmer vilka uppgifter som läggs in och varför.</p>
        <p>Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta, är personuppgiftsbiträde. Vi behandlar uppgifterna för verkstadens räkning.</p>
        <p>Avtalet är en del av <a href="{{ route('terms') }}">de allmänna villkoren</a>. Verkstaden godkänner det tillsammans med villkoren när kontot skapas, och det gäller från den dagen.</p>

        <h2>2. Behandlingens föremål, varaktighet, art och ändamål</h2>
        <p>Vi lagrar och visar uppgifter som verkstaden lägger in i Bågspår: projekt, bågar, bilder, offerter, statuslänkar och leveransgodkännanden.</p>
        <p>Ändamålet är att verkstaden ska kunna hålla ordning på sitt arbete och visa det för sina kunder. Vi behandlar uppgifterna bara på verkstadens instruktion.</p>
        <p>Behandlingen pågår så länge kontot finns.</p>

        <h2>3. Kategorier av registrerade och personuppgifter</h2>
        <p>De registrerade är:</p>
        <ul>
            <li>verkstadens kunder och deras kontaktpersoner</li>
            <li>verkstadens egna användare</li>
        </ul>
        <p>Personuppgifterna kan vara:</p>
        <ul>
            <li>namn, e-postadress, telefonnummer och adress</li>
            <li>bilder på fönster, som kan visa delar av en bostad</li>
            <li>namn som anges när en kund godkänner en offert eller en leverans, med tidpunkt</li>
            <li>användarens namn och e-postadress</li>
        </ul>
        <p>Verkstaden ska inte lägga in känsliga personuppgifter eller personnummer.</p>

        <h2>4. Biträdets skyldigheter</h2>
        <p>Prodi Digital AB:</p>
        <ul>
            <li>behandlar personuppgifterna bara enligt verkstadens dokumenterade instruktioner. Verkstadens användning av tjänsten är instruktionen.</li>
            <li>ser till att personal som har tillgång till uppgifterna har tystnadsplikt.</li>
            <li>skyddar uppgifterna med säkerhetsåtgärder: krypterad överföring med TLS, åtkomst som är avgränsad per verkstadskonto, privat lagring av bilder, säkerhetskopior och loggning.</li>
            <li>hjälper verkstaden att svara på de registrerades begäran. Verkstaden kan själv exportera och radera en kunds uppgifter i appen. Vi hjälper dessutom till på begäran.</li>
            <li>hjälper verkstaden med säkerhet, incidenter och konsekvensbedömningar i den mån det behövs och vi har uppgifterna.</li>
            <li>raderar eller lämnar tillbaka uppgifterna när kontot avslutas, inom 30 dagar, om inte lag kräver att vi sparar något.</li>
            <li>ger verkstaden den information som behövs för att visa att avtalet följs, och medverkar vid granskningar. Verkstaden meddelar oss i rimlig tid i förväg.</li>
        </ul>
        <p>Om vi anser att en instruktion strider mot dataskyddsreglerna berättar vi det för verkstaden.</p>

        <h2>5. Underbiträden</h2>
        <p>Verkstaden ger oss ett allmänt tillstånd att använda dessa underbiträden:</p>
        <ul>
            <li>Laravel Forge-hanterade servrar inom EU: hosting av appen, databasen och de privata bildfilerna.</li>
            <li>Cloudflare: CDN och proxy framför webbplatsen. Trafiken går genom Cloudflare.</li>
            <li>Postmark: utskick av e-post från tjänsten, till exempel inbjudningar och offerter.</li>
        </ul>
        <p>Listan hålls uppdaterad på den här sidan. Vi meddelar ändringar med e-post 30 dagar i förväg. Verkstaden har rätt att invända mot ett nytt underbiträde inom den tiden. Vi sätter samma dataskyddskrav på underbiträdena som i det här avtalet, och vi ansvarar för dem gentemot verkstaden.</p>

        <h2>6. Personuppgiftsincidenter</h2>
        <p>Om vi upptäcker en personuppgiftsincident som berör verkstadens uppgifter meddelar vi verkstaden utan onödigt dröjsmål. Det sker senast inom 48 timmar efter att vi upptäckt den.</p>
        <p>Vi beskriver vad som hänt, vilka uppgifter det gäller och vad vi gör åt det, så långt vi vet. Verkstaden avgör om den ska anmäla till Integritetsskyddsmyndigheten.</p>

        <h2>7. Överföring till tredje land</h2>
        <p>Vi avser inte att föra över personuppgifter utanför EU/EES. Om ett underbiträde ändå gör det, ska EU-kommissionens standardavtalsklausuler eller en annan giltig överföringsmekanism gälla.</p>

        <h2>8. Ansvar och giltighet</h2>
        <p>Villkorens regler om ansvar och tillämplig lag gäller även för det här avtalet.</p>
        <p>Avtalet gäller så länge kontot finns. Skyldigheten att radera eller lämna tillbaka uppgifter gäller även efter det.</p>

        <h2>9. Kontakt</h2>
        <p>Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta.@if (filled(config('bagspar.contact_email'))) Skriv till <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a> med frågor om avtalet, om en registrerads begäran eller om en incident.@endif</p>

        <p>Se även <a href="{{ route('terms') }}">de allmänna villkoren</a> och <a href="{{ route('privacy') }}">integritetspolicyn</a>.</p>
    </div>
</main>
@endsection
