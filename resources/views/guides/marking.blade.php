@extends('layouts.app')

@section('title', 'Märkning av fönsterbågar — Bågspår')
@section('meta_description', 'Så märker du bågen så att den kommer tillbaka till samma öppning: ett nummer, en fysisk lapp som tål färg, och en QR-kod till arbetskortet.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">MÄRKNING</p>
    <h1 class="display-heading">Samma båge, samma öppning</h1>
    <div class="guide-page__body body-copy">
        <p>Märkningen ska överleva skrapning, kitt och färg. Den ska också räcka för att någon annan, en annan dag, sätter bågen i rätt öppning. Utan det blir återmonteringen ett pussel.</p>

        <h2>Ett nummer per båge</h2>
        <p>Ge varje båge ett nummer som inte återanvänds i samma projekt. B-04 är båge fyra, inte ”en av bågarna i köket”. Skriv platsen intill numret: våning, rum och väderstreck eller öppningens läge.</p>
        <p>Exempel: B-04, våning 2, kök, södra. Inner- och ytterbåge får egna nummer, även när de sitter i samma öppning. Annars går det inte att se vilken av dem som ska tillbaka innerst.</p>

        <h2>Lappen på bågen</h2>
        <p>Sätt en fysisk märkning som sitter kvar när bågen målas. En papperslapp i kittet försvinner vid nästa skrapning. Fäst märkningen på ett ställe som inte slipas bort, och skriv samma nummer som på arbetskortet. När färgen är klar ska numret fortfarande gå att läsa.</p>
        <p>Märk bågen innan den lämnar öppningen. En bunt omärkta bågar på golvet går inte att reda ut efteråt, hur tydliga anteckningarna än var.</p>

        <h2>Vanliga misstag</h2>
        <ul>
            <li>två bågar får samma nummer för att de ser likadana ut</li>
            <li>bara rummet skrivs, inte vilken öppning</li>
            <li>märkningen sitter på en del som byts eller skrapas</li>
            <li>inner- och ytterbåge delar nummer</li>
            <li>numret på lappen och numret på kortet har hunnit glida isär</li>
        </ul>

        <h2>QR-koden kompletterar lappen</h2>
        <p>Lappen säger vilket nummer bågen har. QR-koden öppnar <a href="{{ route('guides.work-card') }}">arbetskortet</a>, där plats, bilder, anteckningar och steg ligger. Den som inte minns om B-04 var köket eller sovrummet skannar, i stället för att gissa.</p>
        <p>Lappen och kortet gör olika saker. Lappen följer med bågen genom färg och damm. Kortet håller <a href="{{ route('guides.documentation') }}">dokumentationen</a> som inte får plats på en liten bricka.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection