@extends('layouts.app')

@section('title', 'Efterkalkyl för fönsterrenovering — tid och material per båge — Bågspår')
@section('meta_description', 'Så gör fönsterverkstaden en efterkalkyl: skriv tid och material på varje båge medan arbetet pågår, jämför med offerten och använd siffrorna när nästa offert ska räknas.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('guides.index') }}">← Alla guider</a>
    <p class="eyebrow">EFTERKALKYL</p>
    <h1 class="display-heading">Efterkalkyl för fönsterrenovering</h1>
    <div class="guide-page__body body-copy">
        <p>Offerten är verkstadens gissning om vad ett jobb kostar. Efterkalkylen visar vad det faktiskt kostade. Utan den vet ni inte om ett projekt gick med vinst, och nästa offert bygger på samma gissning igen. Den här guiden går igenom hur en fönsterverkstad kan samla tid och material per båge så att efterkalkylen nästan gör sig själv.</p>

        <h2>Varför per båge och inte per projekt</h2>
        <p>Två bågar i samma hus kan kräva helt olika mycket arbete. En behöver bara skrapas och målas, en annan har rötskadat trä och ett spräckt glas. Skriver ni bara en summa för hela projektet ser ni inte vilka bågar som drog iväg, och ni kan inte jämföra med offerten om den är skriven <a href="{{ route('guides.quote') }}">en rad per båge</a>.</p>
        <p>Med tid och material på varje båge går det att se skillnaden mellan en enkel båge och en svår, och att räkna nästa offert utifrån verkliga siffror från era egna jobb.</p>

        <h2>Skriv tiden samma dag</h2>
        <p>Tid som skrivs i efterhand blir oftast ungefärlig. Det enklaste är att skriva tiden på bågen samma dag som arbetet görs:</p>
        <ul>
            <li>datum,</li>
            <li>timmar och minuter,</li>
            <li>vem som gjorde jobbet,</li>
            <li>en kort anteckning när något tog längre tid än väntat, till exempel "lagning av nedre bågstycke".</li>
        </ul>
        <p>Anteckningen är det som gör siffran begriplig om några månader. Den förklarar varför just den här bågen tog längre tid.</p>

        <h2>Material: typ, mängd och pris</h2>
        <p>Samma sak gäller materialet. Skriv vad som gick åt på bågen: glas, kitt, färg, beslag eller annat, med en beskrivning, mängd och enhet. Har ni ett inköpspris kan ni skriva à-priset också. Då går det att se både hur mycket material ett projekt drog och vad det kostade.</p>
        <p>Materialet summerat för hela projektet fungerar dessutom som inköpslista inför nästa liknande jobb.</p>

        <h2>Jämför med offerten</h2>
        <p>När projektet är klart lägger ni offerten bredvid det som är registrerat:</p>
        <ul>
            <li>Vilka bågar tog mer tid än offerten räknade med, och varför? Titta på anteckningarna.</li>
            <li>Gick det åt mer material än beräknat på någon typ av båge?</li>
            <li>Finns det ett mönster, till exempel att bågar med rötskador alltid tar längre tid än ni räknar med?</li>
        </ul>
        <p>Svaren är det som gör nästa offert bättre. Det handlar inte om att hitta fel, utan om att få offertpriset att närma sig verkligheten.</p>

        <h2>Samma uppgifter ger underlag till rotavdraget</h2>
        <p>Tid och material som redan är registrerade per båge behöver inte samlas ihop en gång till när fakturan ska skrivas. Kom ihåg att det bara är arbete hemma hos kunden som ger rotavdrag, inte tid i verkstaden. Läs mer i guiden om <a href="{{ route('guides.rot') }}">rotavdrag vid fönsterrenovering</a>.</p>

        <h2>Efterkalkyl i Bågspår</h2>
        <p>I Bågspår har varje båge ett arbetskort. Där skriver ni tid med datum, timmar och minuter och en valfri anteckning, och namnet på den som registrerade tiden sparas. På samma kort skriver ni material med typ (glas, kitt, färg, beslag eller övrigt), beskrivning, mängd, enhet och valfritt à-pris.</p>
        <p>Projektets materiallista summerar materialet från alla bågar. I projektrapporten kan ni välja att visa arbetstiden, totalt, per person och per båge. Ni kan också ladda ner en CSV-fil med en rad per båge, med arbetstid i minuter, material och offertens belopp exklusive moms, så att efterkalkylen kan göras i ett kalkylark. ROT-underlaget summerar tid och material per projekt som PDF eller CSV.</p>

        <h2>Checklista för efterkalkylen</h2>
        <ul>
            <li>Är tiden registrerad på varje båge, med datum och vem som gjorde jobbet?</li>
            <li>Har bågar som tog längre tid en anteckning om varför?</li>
            <li>Är materialet skrivet med mängd och enhet, och à-pris där ni har det?</li>
            <li>Har ni jämfört tid och material med offerten, båge för båge?</li>
            <li>Har ni skrivit ner vad ni ska räkna annorlunda i nästa offert?</li>
        </ul>

        <p>Vill ni se vad varje båge faktiskt kostade? <a href="{{ route('register') }}">Prova Bågspår gratis</a> eller <a href="{{ route('contact') }}">kontakta oss</a>.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Efterkalkyl för fönsterrenovering',
    'description' => 'Så gör fönsterverkstaden en efterkalkyl: skriv tid och material på varje båge medan arbetet pågår, jämför med offerten och använd siffrorna när nästa offert ska räknas.',
    'pageUrl' => route('guides.costing'),
    'datePublished' => '2026-11-14',
])
