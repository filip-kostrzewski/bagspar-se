@extends('layouts.app')

@section('title', 'Rapport till bostadsrättsföreningen efter fönsterrenovering — Bågspår')
@section('meta_description', 'Så sammanställer fönsterverkstaden underlaget till en bostadsrättsförening: en rad per båge, bilder före och efter, steg och anteckningar, samlat i en projektrapport som PDF.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('guides.index') }}">← Alla guider</a>
    <p class="eyebrow">PROJEKTRAPPORT</p>
    <h1 class="display-heading">Rapport till bostadsrättsföreningen efter fönsterrenovering</h1>
    <div class="guide-page__body body-copy">
        <p>När en fönsterrenovering görs åt en bostadsrättsförening är det sällan en person som tar emot arbetet. Styrelsen har beställt, boende har frågor om sina egna fönster, och den som ska följa upp arbetet om några år var kanske inte med när det gjordes. Rapporten är det som blir kvar. Den ska visa vilka bågar som har renoverats, vad som gjordes på varje båge och hur det såg ut innan.</p>
        <p>Rapporten är ett underlag. Den visar vad verkstaden har skrivit in. Den lovar inte hur länge målningen håller, och den ersätter inte föreningens egen besiktning.</p>

        <h2>Vad föreningen brukar behöva veta</h2>
        <p>Styrelsen frågar oftast samma saker när arbetet är klart:</p>
        <ul>
            <li>Vilka lägenheter och vilka fönster som ingick, och vilka som inte gjorde det.</li>
            <li>Vad som gjordes på varje båge: skrapning, trälagning, glasning, målning.</li>
            <li>Vilka rutor som byttes och vilka originalrutor som sitter kvar.</li>
            <li>Hur bågen såg ut innan arbetet, så att det går att jämföra.</li>
            <li>Vad som återstår, om något inte blev klart.</li>
        </ul>
        <p>Om svaret på de frågorna finns i verkstaden men inte i rapporten, får någon leta i mobilen, i mejl och på lappar i efterhand. Det är lättare att skriva det på rätt båge medan arbetet pågår.</p>

        <h2>En rad per båge, inte en rad per lägenhet</h2>
        <p>En förening tänker i lägenheter. Verkstaden arbetar med bågar. Rapporten behöver båda. Skriv lägenhetens nummer eller trapphus i projektets anteckning eller i platsen på kortet, och låt varje båge ha sitt eget nummer, till exempel 2-KÖK-F1-B2: våning 2, köket, första fönstret från vänster sett inifrån, vänster innerbåge.</p>
        <p>Då går det att läsa rapporten åt två håll. Styrelsen kan hitta en lägenhets fönster, och verkstaden kan hitta en enskild båge. Hur numret byggs upp står i guiden om <a href="{{ route('guides.marking') }}">märkning av fönsterbågar</a>.</p>

        <h2>Bilder före och efter, på rätt båge</h2>
        <p>Ta före-bilden vid öppningen innan något skrapas, och en närbild på det som anteckningen nämner: kitt som släppt, mjukt trä, en sprucken ruta. Ta efter-bilden när momentet är gjort. Lägg bilden på den båge den visar, samma dag.</p>
        <p>En gemensam mapp med hundra bilder från en fasad hjälper inte styrelsen. En bild som ligger på kortet för 3-VARD-F2-B1 gör det. Mer om bilderna står i guiden om att <a href="{{ route('guides.document-renovation') }}">dokumentera fönsterrenoveringen</a>, och hur skicket skrivs och godkänns vid hämtningen står i guiden om <a href="{{ route('guides.intake-protocol') }}">mottagningsprotokoll för fönsterbågar</a>.</p>

        <h2>Steg och anteckningar som går att lita på</h2>
        <p>Varje båge har ett steg: mottagen, skrapning, trälagning, glasning, målning eller klar. Klar betyder att bågen kan lämna verkstaden mot sin öppning, inte att den redan sitter i väggen. Flytta inte steget i förväg, och skriv vad som återstår om något inte är gjort.</p>
        <p>Anteckningen är det föreningen läser. Skriv korta rader som går att förstå utan verkstadens förkortningar: nedre högra rutan bytt, originalrutan sparad, kulör enligt föreningens beslut. Saknas en uppgift på kortet saknas den också i rapporten.</p>

        <h2>Projektrapporten</h2>
        <p>När arbetet ska visas samlas korten i en projektrapport. I Bågspår innehåller den projektets namn, plats, antal bågar, anteckningar och för varje båge dess nummer, rum, steg och bilder. Rapporten kan laddas ner som PDF eller skrivas ut och lämnas till styrelsen.</p>
        <p>Under arbetet kan kunden få en separat statuslänk som visar hur det går, utan verkstadens interna anteckningar. Hur korten används i verkstaden beskrivs på sidan om <a href="{{ route('guides.work-card') }}">arbetskort för fönsterbågar</a>.</p>

        <h2>Checklista innan rapporten lämnas</h2>
        <ul>
            <li>Varje båge i projektet har ett eget kort med nummer och plats.</li>
            <li>Före-bilder finns på de bågar där skicket har noterats.</li>
            <li>Steget stämmer med vad som faktiskt är gjort.</li>
            <li>Bytta rutor och sparade originalrutor står i anteckningen.</li>
            <li>Det som återstår är skrivet, inte utelämnat.</li>
        </ul>

        <p>Vill ni prova på ett pågående projekt? <a href="{{ route('register') }}">Prova Bågspår gratis</a> eller <a href="{{ route('contact') }}">kontakta oss</a>.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Rapport till bostadsrättsföreningen efter fönsterrenovering',
    'description' => 'Så sammanställer fönsterverkstaden underlaget till en bostadsrättsförening: en rad per båge, bilder före och efter, steg och anteckningar, samlat i en projektrapport som PDF.',
    'pageUrl' => route('guides.brf-report'),
    'datePublished' => '2026-10-27',
])
