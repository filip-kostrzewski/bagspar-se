@extends('layouts.app')

@section('title', 'Rotavdrag vid fönsterrenovering — det verkstaden behöver hålla isär — Bågspår')
@section('meta_description', 'Vilken del av en fönsterrenovering som ger rotavdrag enligt Skatteverket, varför tiden i verkstaden inte räknas, och hur ni håller isär tid på plats, tid i verkstaden och material per båge.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('guides.index') }}">← Alla guider</a>
    <p class="eyebrow">ROTAVDRAG</p>
    <h1 class="display-heading">Rotavdrag vid fönsterrenovering</h1>
    <div class="guide-page__body body-copy">
        <p>Många kunder räknar med rotavdrag när de beställer en fönsterrenovering. För en verkstad som tar med bågarna hem och renoverar dem där blir avdraget ofta mindre än kunden tror. Det beror inte på hur fakturan skrivs, utan på var arbetet görs. Den här guiden går igenom vad Skatteverket säger och vad verkstaden behöver hålla isär för att fakturan och ansökan ska stämma.</p>
        <p>Guiden är en sammanfattning av Skatteverkets information, inte skatterådgivning. Kontrollera alltid aktuella regler hos <a href="https://www.skatteverket.se/foretag/skatterochavdrag/rotochrut/safungerarrotavdraget.4.2ef18e6a125660db8b080002709.html" rel="noopener">Skatteverket</a>.</p>

        <h2>Grunderna</h2>
        <ul>
            <li>Rotavdraget är högst 30 procent av arbetskostnaden inklusive moms.</li>
            <li>Material, resor, maskiner och administration ger inget avdrag.</li>
            <li>Kunden kan få högst 50 000 kronor i rotavdrag per år, och rot och rut tillsammans högst 75 000 kronor.</li>
            <li>Arbetet ska vara utfört och betalt, elektroniskt, innan verkstaden begär utbetalning. Begäran ska ha kommit in senast den 31 januari året efter att kunden betalade.</li>
        </ul>

        <h2>Tiden i verkstaden ger inget avdrag</h2>
        <p>Skatteverket skriver att det endast är arbetad tid på plats hos kunden som ger rätt till rotavdrag. Bland arbeten som inte ger avdrag nämns uttryckligen glasarbeten utanför kundens bostad, till exempel i företagets egna lokaler.</p>
        <p>För en fönsterrenovering betyder det i praktiken att tid som läggs hos kunden, som att demontera och montera bågarna och arbeta med karmarna på plats, kan ge avdrag. Skrapning, kittning, målning och glasning av bågar i verkstaden ger det inte. Läs mer under <a href="https://www.skatteverket.se/foretag/skatterochavdrag/rotochrut/gerarbetetratttillrotavdrag.4.5c1163881590be297b5173bf.html" rel="noopener">Ger arbetet rätt till rotavdrag?</a></p>

        <h2>Bostadsrätt: bara insidan</h2>
        <p>I en bostadsrätt ger bara arbete inne i lägenheten rotavdrag, och för fönster gäller det insidan. Skatteverket nämner byte av glaset i den inre bågen, målning av bågens insida och byte eller montering av tätningslist. Arbete på fasaden ger inget avdrag, och det gäller även om stadgarna säger att medlemmen ska underhålla den delen. När föreningen själv beställer renoveringen finns ingen privatperson som kan få rotavdrag.</p>

        <h2>Det som ska stå isär på fakturan</h2>
        <p>Skatteverket räknar upp vad fakturan bör innehålla vid rotavdrag:</p>
        <ul>
            <li>Fakturans totala belopp och rotavdragets storlek, räknat inklusive moms.</li>
            <li>Arbetskostnad, material och övriga kostnader var för sig.</li>
            <li>Vilken typ av arbete som har utförts.</li>
            <li>Att företaget har F-skatt.</li>
            <li>Kundens namn och personnummer.</li>
            <li>Fastighetsbeteckning, eller föreningens organisationsnummer och lägenhetsnummer för en bostadsrätt.</li>
        </ul>
        <p>I ansökan anger verkstaden det faktiska antalet arbetade timmar, även när arbetet är gjort till fast pris. Utan tidsrader blir det svårt att visa vilka timmar som gjordes på plats. Priset i offerten bör redan stå före rotavdrag, vilket guiden om <a href="{{ route('guides.quote') }}">offert för fönsterrenovering</a> går igenom.</p>

        <h2>Skriv tiden per båge och plats samma dag</h2>
        <p>Det enklaste sättet att hålla isär tiden är att skriva den direkt när den görs: vilken båge, vem, hur länge och om det var på plats eller i verkstaden. Material skrivs på samma sätt, med mängd och pris. Då går det att ta fram arbetskostnaden för tiden på plats utan att gissa i efterhand.</p>

        <h2>Tid och material i Bågspår</h2>
        <p>I Bågspår skrivs tid och material på varje bågs arbetskort: datum, person, tid och en anteckning, och material med mängd och à-pris. Med verkstadens timpris summeras det till ett ROT-underlag per projekt, med varje tidsrad och materialrad, som ni laddar ner som PDF eller CSV.</p>
        <p>Underlaget räknar inte ut rotavdraget och skiljer inte själv på tid på plats och tid i verkstaden. Skriv därför "på plats" i anteckningen på de tidsrader som gjordes hos kunden, så syns de direkt i underlaget när fakturan ska skrivas. Själva avdraget räknar ni som vanligt på fakturan. Tidsraderna följer också med till <a href="{{ route('guides.brf-report') }}">projektrapporten</a>, och bilderna före från <a href="{{ route('guides.intake-protocol') }}">mottagningsprotokollet</a> visar skicket när bågarna hämtades.</p>

        <h2>Checklista före fakturan</h2>
        <ul>
            <li>Är tiden på plats skild från tiden i verkstaden?</li>
            <li>Är materialet skrivet för sig, med pris?</li>
            <li>Har ni kundens personnummer och fastighetsbeteckning, eller föreningens organisationsnummer och lägenhetsnummer?</li>
            <li>Gäller arbetet i en bostadsrätt bara insidan av fönstren?</li>
            <li>Betalar kunden elektroniskt, och hinner ni begära utbetalning senast den 31 januari året efter betalningen?</li>
        </ul>

        <p>Vill ni ha tid och material per båge samlade inför nästa faktura? <a href="{{ route('register') }}">Prova Bågspår gratis</a> eller <a href="{{ route('contact') }}">kontakta oss</a>.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Rotavdrag vid fönsterrenovering',
    'description' => 'Vilken del av en fönsterrenovering som ger rotavdrag enligt Skatteverket, varför tiden i verkstaden inte räknas, och hur ni håller isär tid på plats, tid i verkstaden och material per båge.',
    'pageUrl' => route('guides.rot'),
    'datePublished' => '2026-11-03',
])
