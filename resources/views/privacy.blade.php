@extends('layouts.app')

@section('title', 'Integritetspolicy — Bågspår')
@section('meta_description', 'Integritetspolicy för Bågspår, med information om intresseanmälan och uppgifter som behandlas i verkstadens konto.')

@section('content')
@php
    $googleAnalyticsMeasurementId = \App\Support\GoogleAnalyticsMeasurementId::normalize(
        config('services.google_analytics.measurement_id'),
    );
@endphp
<main id="main" class="privacy-page page-shell">
    <a class="text-link privacy-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">INTEGRITET</p>
    <h1 class="display-heading">Integritetspolicy</h1>
    <div class="privacy-page__body body-copy">
        <p>Den här sidan beskriver hur vi behandlar personuppgifter när du anmäler en verkstad till Bågspår eller skickar ett meddelande via kontaktformuläret, enligt dataskyddsförordningen (GDPR).</p>

        <h2>Personuppgiftsansvarig</h2>
        <p>Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta, är personuppgiftsansvarig för uppgifterna i anmälan.</p>

        <h2>Uppgifter vi sparar från anmälan</h2>
        <p>När du anmäler en verkstad sparar vi:</p>
        <ul>
            <li>verkstadens eller företagets namn</li>
            <li>e-postadress</li>
            <li>tidpunkten då anmälan skickades</li>
        </ul>
        <p>Anmälan har inga andra fält. Den ber inte om telefonnummer, personnummer eller en beskrivning av ert arbetssätt.</p>

        <h2>Varför vi behandlar uppgifterna</h2>
        <p>Uppgifterna används för att ta emot anmälan och kontakta dig om Bågspår. Den rättsliga grunden är artikel 6.1 b i dataskyddsförordningen: åtgärder på din begäran innan avtal. Vi säljer inte uppgifterna.</p>

        <h2>Hur länge uppgifterna sparas</h2>
        <p>Vi sparar uppgifterna så länge de behövs för att följa upp anmälan. Du kan när som helst kontakta oss och be att uppgifterna raderas.</p>

        <h2>Meddelanden via kontaktformuläret</h2>
        <p>När du skriver till oss via kontaktformuläret sparar vi namn, e-postadress, meddelande och tidpunkt. Företag och telefonnummer sparas bara om du fyller i dem. Uppgifterna används för att läsa och besvara meddelandet. Den rättsliga grunden är artikel 6.1 b i dataskyddsförordningen: åtgärder på din begäran. Vi sparar dem så länge de behövs för att hantera meddelandet. Du kan be oss radera dem.</p>

        <h2>Vilka som tar del av uppgifterna</h2>
        <p>Uppgifterna sparas i Bågspårs databas och i ett internt e-postmeddelande till oss när en anmälan kommer in. Webbserverns vanliga tekniska loggar kan innehålla IP-adress och tidpunkt. De används för drift och säkerhet.</p>

        <h2>Cookies</h2>
        <p>Formuläret använder en teknisk sessionscookie som behövs för att skicka anmälan. Den kräver inte samtycke.</p>
        @if ($googleAnalyticsMeasurementId !== null)
            <p>Om du godkänner det använder vi Google Analytics 4 för att se hur webbplatsen används, till exempel vilka sidor som besöks. Google Ireland Limited tar emot mätningen. Den kan föras vidare till Google LLC i USA. Vi använder den inte till annonser.</p>
            <p>Innan du väljer är lagringen nekad: analytics_storage, ad_storage, ad_user_data och ad_personalization. Inga analys- eller annonscookies sätts då. Taggen kan ändå skicka en begränsad teknisk signal utan cookies.</p>
            <p>Väljer du Godkänn kan Google sätta cookies för besöksstatistik, bland annat _ga och en cookie som börjar med _ga_, i upp till två år. ad_storage, ad_user_data och ad_personalization förblir nekade. Väljer du Neka förblir även analysen nekad. Valet sparas i den här webbläsaren. Du kan ändra det när som helst via Cookie-inställningar i sidfoten. Den rättsliga grunden är samtycke, artikel 6.1 a i dataskyddsförordningen.</p>
        @else
            <p>Webbplatsen använder inga analysverktyg och inga spårningscookies.</p>
        @endif

        <h2>Exempelarbetskortet</h2>
        <p>Exemplet med testuppgifter är påhittat. Om du ändrar steg eller skriver en anteckning där sparas det i din webbläsare. När det finns en anslutning skickas steget och anteckningarna till en tillfällig cache kopplad till en slumpad nyckel i samma webbläsare, så att ändringen finns kvar om du laddar om sidan. Bilder du lägger till stannar i webbläsaren. Cachen raderas senast efter {{ config('bagspar.demo_sync_days') }} dagar. Vi använder inte de uppgifterna för att kontakta dig, och de kopplas inte till anmälan.</p>
        <p>Kortet kan också lagras i webbläsarens cache så att det går att öppna igen utan täckning. Det är ingen spårning.</p>

        <h2>Uppgifter i verkstadens konto</h2>
        <p>När en verkstad använder kontot behandlar vi uppgifter som behövs för att hålla ordning på arbetet:</p>
        <ul>
            <li>kontots namn, e-postadress och verkstadens namn</li>
            <li>projektets namn, kund eller beställare, plats och anteckningar</li>
            <li>varje båges kod, rum, placering, aktuella arbetssteg och anteckningar</li>
            <li>bilder som laddas upp till en båge, med originalfilnamn, filtyp och filstorlek</li>
            <li>faktureringsuppgifter när verkstaden beställer abonnemang: organisationsnummer, fakturamejl, vald betalningsperiod och en eventuell referens</li>
        </ul>
        <p>Uppgifterna används för att visa och hantera projekt, arbetskort, bilder och projektrapporter i Bågspår, och faktureringsuppgifterna för att skicka fakturan. Fakturor bokförs hos vår redovisningsleverantör och sparas så länge bokföringslagen kräver. Kontots uppgifter och bilder hör till verkstaden. De visas efter inloggning för användare med åtkomst till verkstadens konto. Bildfilerna är privata och visas via en skyddad appfunktion, inte som publika bildlänkar. Om verkstaden slår på en statuslänk för ett projekt kan den som har länken se bågarnas etapp och bilder märkta före eller efter. Anteckningar ingår inte, och länken kan stängas av. Om den som har länken godkänner leveransen sparas namnet och tidpunkten på projektet. Det gäller också om kunden godkänner mottagningsprotokollet, antingen via länken eller på plats hos verkstaden. Om kunden godkänner en offert via länken sparas namnet och tidpunkten på offerten.</p>
        <p>För uppgifterna om verkstadens kunder är Prodi Digital AB personuppgiftsbiträde enligt <a href="{{ route('dpa') }}">personuppgiftsbiträdesavtalet</a>. Verkstaden är personuppgiftsansvarig, så kontakta verkstaden om dessa uppgifter.</p>
        <p>Om du vill få uppgifter i ett konto raderade, kontakta oss via e-postadressen nedan. Appen har för närvarande ingen egen funktion för att radera konto eller enskilda arbetskort.</p>

        <h2>Dina rättigheter</h2>
        <p>Du har rätt att:</p>
        <ul>
            <li>få veta vilka uppgifter vi har om dig och få en kopia</li>
            <li>få felaktiga uppgifter rättade</li>
            <li>få uppgifterna raderade</li>
            <li>begära att behandlingen begränsas</li>
            <li>invända mot behandlingen</li>
            <li>få ut uppgifterna i ett maskinläsbart format</li>
        </ul>
        <p>Du kan också lämna ett klagomål till Integritetsskyddsmyndigheten, <a href="https://www.imy.se">imy.se</a>.</p>

        @if (filled(config('bagspar.contact_email')))
            <h2>Kontakt och radering</h2>
            <p>Skriv till <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a> om du vill att vi raderar uppgifterna, om du vill använda någon annan av dina rättigheter, eller om du har en fråga om uppgifterna.</p>
        @endif

        <p class="privacy-page__updated">Senast uppdaterad 17 oktober 2026.</p>
    </div>
</main>
@endsection
