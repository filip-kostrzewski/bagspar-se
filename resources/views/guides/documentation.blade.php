@extends('layouts.app')

@section('title', 'Dokumentation vid fönsterrenovering — Bågspår')
@section('meta_description', 'Vilka uppgifter som ska följa med varje båge från demontering till återmontering: plats, skick, glas, material, bilder och arbetssteg.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">DOKUMENTATION</p>
    <h1 class="display-heading">Vad som ska följa med bågen</h1>
    <div class="guide-page__body body-copy">
        <p>När bågen lämnar öppningen försvinner det som satt i väggens minne. Rum, sida, skick och vad som ska göras måste följa med in i verkstaden. Annars hamnar rätt båge i fel öppning när den ska tillbaka.</p>

        <h2>Innan bågen lämnar väggen</h2>
        <p>Skriv ner det här medan du står vid öppningen, innan bågen läggs i bunten:</p>
        <ul>
            <li>projekt eller adress</li>
            <li>våning, rum och vilken öppning det är</li>
            <li>inner- eller ytterbåge, och om det är en över- eller underbåge</li>
            <li>skick: kitt, färg, trä och beslag</li>
            <li>glas: antal rutor, sprickor, och om glaset ska tas ur</li>
            <li>material som redan är bestämt, till exempel linoljekitt eller en viss kulör</li>
            <li>en bild av bågen på plats, innan den tas ur</li>
            <li>det första arbetssteget, så nästa person ser var den ska börja</li>
        </ul>

        <h2>Där det brukar bli fel</h2>
        <p>Två bågar från samma rum kan se likadana ut när färgen är skrapad. Utan plats och bild blir återmonteringen en gissning. En lapp som bara säger ”kök” räcker inte när köket har fyra öppningar, och den räcker inte när inner- och ytterbåge ska tillbaka i par.</p>
        <p>Det som saknas vid demonteringen går sällan att återskapa vid bänken. Ingen minns vilken av de åtta bågarna som hade sprucket glas i den nedre rutan, om det inte skrevs ner då.</p>

        <h2>Så håller kortet ihop det</h2>
        <p>I Bågspår ligger uppgifterna på bågens arbetskort. Numret på kortet är samma som på <a href="{{ route('guides.marking') }}">märkningen av fönsterbågen</a>. Bilderna och anteckningarna stannar på den bågen när steget flyttas från inlämnad till skrapning, kittning, målning och klar.</p>
        <p>Den som tar vid behöver inte leta i en pärm. Kortet visar plats, skick och vad som redan är gjort. Läs mer om <a href="{{ route('guides.work-card') }}">arbetskort för fönsterbågar</a>.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection
