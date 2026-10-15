@extends('layouts.app')

@section('title', 'Integritetspolicy — Bågspår')
@section('meta_description', 'Integritetspolicy för Bågspår. Prodi Digital AB beskriver vilka uppgifter som sparas när en verkstad anmäler sig.')

@section('content')
<main id="main" class="privacy-page page-shell">
    <a class="text-link privacy-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">INTEGRITET</p>
    <h1 class="display-heading">Integritetspolicy</h1>
    <div class="privacy-page__body body-copy">
        <p>Den här sidan beskriver hur vi behandlar personuppgifter när du anmäler en verkstad till Bågspår, enligt dataskyddsförordningen (GDPR).</p>

        <h2>Personuppgiftsansvarig</h2>
        <p>Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta, är personuppgiftsansvarig för uppgifterna i anmälan.</p>

        <h2>Uppgifter vi sparar från anmälan</h2>
        <p>När du anmäler en verkstad sparar vi:</p>
        <ul>
            <li>verkstadens eller företagets namn</li>
            <li>e-postadress</li>
            <li>tidpunkten då anmälan skickades</li>
        </ul>
        <p>Formuläret har inga andra fält. Vi ber inte om telefonnummer, personnummer eller en beskrivning av ert arbetssätt. En tidigare anmälan kan innehålla en valfri text som verkstaden själv skrev. Den texten behandlas på samma sätt som övriga uppgifter och raderas när anmälan raderas.</p>

        <h2>Varför vi behandlar uppgifterna</h2>
        <p>Uppgifterna används för att ta emot anmälan, ge tillgång till prototypen och kontakta dig om piloten. Den rättsliga grunden är artikel 6.1 b i dataskyddsförordningen: åtgärder på din begäran innan avtal om piloten. Vi säljer inte uppgifterna.</p>

        <h2>Hur länge uppgifterna sparas</h2>
        <p>Vi sparar uppgifterna så länge de behövs för att följa upp anmälan och piloten. Om du inte går vidare raderar vi dem senast 24 månader efter anmälan. Ber du oss radera dem tidigare gör vi det.</p>

        <h2>Vilka som tar del av uppgifterna</h2>
        <p>Uppgifterna sparas i Bågspårs databas och i ett internt e-postmeddelande till oss när en anmälan kommer in. Webbplatsen använder inga analysverktyg och inga spårningscookies. Webbserverns vanliga tekniska loggar kan innehålla IP-adress och tidpunkt. De används för drift och säkerhet.</p>

        <h2>Exempelarbetskortet</h2>
        <p>Exempelarbetskortet är påhittat. Om du ändrar steg eller skriver en anteckning där sparas det i din webbläsare. När det finns en anslutning skickas steget och anteckningarna till en tillfällig cache kopplad till en slumpad nyckel i samma webbläsare, så att ändringen finns kvar om du laddar om sidan. Bilder du lägger till stannar i webbläsaren. Cachen raderas senast efter {{ config('bagspar.demo_sync_days') }} dagar. Vi använder inte de uppgifterna för att kontakta dig, och de kopplas inte till anmälan.</p>
        <p>Kortet kan också lagras i webbläsarens cache så att det går att öppna igen utan täckning. Det är ingen spårning.</p>

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
            <h2>Kontakt</h2>
            <p>Kontakta oss på <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a> om du vill använda dina rättigheter eller har en fråga om uppgifterna.</p>
        @endif

        <p class="privacy-page__updated">Senast uppdaterad 15 oktober 2026.</p>
    </div>
</main>
@endsection
