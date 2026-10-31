@extends('layouts.app')

@section('title', 'Mottagningsprotokoll för fönsterbågar — Bågspår')
@section('meta_description', 'Så dokumenterar fönsterverkstaden skicket på varje båge när den hämtas: anmärkning och bild före per båge, och kundens godkännande via länk eller på plats, samlat i ett protokoll som PDF.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('guides.index') }}">← Alla guider</a>
    <p class="eyebrow">MOTTAGNINGSPROTOKOLL</p>
    <h1 class="display-heading">Mottagningsprotokoll för fönsterbågar</h1>
    <div class="guide-page__body body-copy">
        <p>När bågarna lyfts ur karmen och åker till verkstaden är det sista gången kunden ser dem i sitt gamla skick. Sprickor i rutor, ruttet trä i nederstycket, kitt som redan har släppt och beslag som saknas finns där innan verkstaden har rört något. Om det inte skrivs ner då, blir det i efterhand ord mot ord.</p>
        <p>Ett mottagningsprotokoll löser det. Det visar hur varje båge såg ut när verkstaden tog emot den, och att kunden har sett samma sak.</p>

        <h2>Vad protokollet ska svara på</h2>
        <ul>
            <li>Vilka bågar som hämtades, med nummer och plats, så att inget saknas eller förväxlas.</li>
            <li>Vilket skick varje båge hade: spruckna eller lösa rutor, rötskador, löst kitt, trasiga eller saknade beslag, tidigare lagningar.</li>
            <li>Hur bågen såg ut, med en bild före arbetet.</li>
            <li>Att kunden har tagit del av skicket, med namn och tidpunkt.</li>
        </ul>
        <p>Protokollet är inget kontrakt och ingen besiktning. Det är verkstadens egen anteckning om vad den såg vid mottagningen, som kunden bekräftar.</p>

        <h2>Skriv skicket per båge, inte per fasad</h2>
        <p>"Några rutor spruckna" hjälper ingen när en kund senare frågar om en viss ruta. Skriv anmärkningen på den båge den gäller: "nedre vänstra rutan sprucken i hörnet", "nederstycket mjukt längst ut till höger", "ett hörnjärn saknas". Har bågen inga anmärkningar går det att låta fältet vara tomt, men bilden före bör ändå finnas.</p>
        <p>Bågen behöver ett eget nummer som också står på bågen. Hur numret byggs upp står i guiden om <a href="{{ route('guides.marking') }}">märkning av fönsterbågar</a>.</p>

        <h2>Bilden före tas innan något görs</h2>
        <p>Ta bilden vid öppningen eller direkt när bågen kommer in, innan skrapning eller demontering. Ta en helbild på bågen och en närbild på det som anteckningen nämner. Lägg bilden på bågens kort samma dag, inte i en gemensam mapp. Mer om bilderna står i guiden om att <a href="{{ route('guides.document-renovation') }}">dokumentera fönsterrenoveringen</a>.</p>

        <h2>Låt kunden godkänna innan arbetet börjar</h2>
        <p>Protokollet gör mest nytta när kunden har sett det innan verkstaden börjar arbeta. Kunden kan gå igenom bågarna på plats när de hämtas, eller få en länk och titta senare. I båda fallen sparas vem som godkände och när.</p>
        <p>Om kunden har frågor är det enklare att reda ut dem nu än när bågen redan är skrapad och skicket inte längre syns.</p>

        <h2>Mottagningsprotokollet i Bågspår</h2>
        <p>I Bågspår skrivs skicket vid mottagning direkt på varje bågs arbetskort, och bilderna före läggs på samma kort. På projektsidan visas hur många bågar som har anmärkningar och hur många som har bilder före. Protokollet kan visas eller laddas ner som PDF.</p>
        <p>Kunden godkänner via statuslänken. Godkänner kunden på plats hos er skriver ni in kundens namn, och tidpunkten sparas i protokollet. Samma uppgifter följer sedan med till <a href="{{ route('guides.brf-report') }}">projektrapporten</a> när arbetet är klart.</p>

        <h2>Checklista vid mottagning</h2>
        <ul>
            <li>Varje båge har ett nummer, både på bågen och på kortet.</li>
            <li>Bågens plats är angiven: våning, rum, fönster.</li>
            <li>Skador och avvikelser är skrivna på rätt båge.</li>
            <li>Det finns en bild före på varje båge, och en närbild där något är antecknat.</li>
            <li>Kunden har godkänt protokollet, via länken eller på plats.</li>
        </ul>

        <p>Vill ni prova på nästa hämtning? <a href="{{ route('register') }}">Prova Bågspår gratis</a> eller <a href="{{ route('contact') }}">kontakta oss</a>.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Mottagningsprotokoll för fönsterbågar',
    'description' => 'Så dokumenterar fönsterverkstaden skicket på varje båge när den hämtas: anmärkning och bild före per båge, och kundens godkännande via länk eller på plats, samlat i ett protokoll som PDF.',
    'pageUrl' => route('guides.intake-protocol'),
    'datePublished' => '2026-10-31',
])
