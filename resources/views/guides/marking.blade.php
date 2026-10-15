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
        <p>Lappen och numret gör två olika saker. Lappen sitter på träet och går att läsa när telefonen är undanlagd. Numret är detsamma på <a href="{{ route('guides.work-card') }}">arbetskortet</a>, där plats, bilder och steg ligger. Stämmer de inte överens är märkningen inte färdig.</p>

        <h2>Så märker du bågen</h2>
        <h3>1. Bestäm räkningen innan första bågen lossas</h3>
        <p>Gör upp hur våning, rum, fönster och båge räknas, och håll den räkningen genom hela projektet. Byt inte mellan att räkna fasaden utifrån och rummet inifrån. Skriv räkningen på första kortet. Schemat bakom 2-KÖK-F1-B2 förklaras längre ner.</p>

        <h3>2. Ge varje båge ett eget nummer</h3>
        <p>Varje båge som lämnar karmen får ett nummer som inte återanvänds i projektet. Inner- och ytterbåge är två bågar, vänster och höger luft två till. Ett överljus som tas ur för sig får nästa lediga bågnummer i samma fönster, med ordet överljus i anteckningen. En fönsterdörr är en egen öppning och får ett eget F-nummer. Lös spröjs följer bågen den sitter i.</p>

        <h3>3. Skriv numret på lappen och på kortet i samma stund</h3>
        <p>Skriv hela numret, inte bara B2. När flera rum är inne finns det en B2 i varje fönster. 2-KÖK-F1-B2 kan inte blandas ihop med 2-SOV1-F1-B2. Skriv samma tecken på lappen och i kortets rubrik, innan bågen ställs i bunten. En omärkt bunt går inte att reda ut efteråt, hur tydlig <a href="{{ route('guides.documentation') }}">dokumentationen</a> än var vid öppningen.</p>

        <h3>4. Fäst märkningen där skrapning och färg inte tar den</h3>
        <p>En papperslapp i kittfalsen försvinner vid nästa skrapning. Fäst en bricka eller en tejp som tål färg på trä som inte ska skrapas rent, till exempel på gångjärnssidan om färgborttagningen inte tar där. Välj ett ställe ni upprepar. Numret ska gå att läsa efter målning. Täcker färgen det, skriv om det innan bågen lämnar målarplatsen.</p>

        <h3>5. Håll ihop paret utan att ge dem samma nummer</h3>
        <p>Ytterbåge och innerbåge ska tillbaka i samma öppning, men de är inte samma arbetsstycke. Ge dem B1 och B2, eller B3 och B4 i den högra luften, och skriv den andra bågens nummer i anteckningen. Märk också glaset om en ruta tas ur, med samma bågnummer, så att originalglas inte byter fals.</p>

        <h3>6. Läs numret igen när färgen är klar</h3>
        <p>Innan bågen lämnar verkstaden läser du lappen och kortet mot varandra. Hela numret ska stämma. 2-KÖK-F1-B2 är vänster innerbåge, inte höger ytterbåge. Stämmer de inte, rätta det innan bågen lastas.</p>

        <h2>Så läses numret</h2>
        <p>2-KÖK-F1-B2 delas i fyra led. 2 är våningen. KÖK är rummet. F1 är fönstret. B2 är bågen. Inget av leden går att hoppa över. Utan våning blandas kök på olika plan. Utan F blandas öppningarna i rummet. Utan B blandas bågarna i öppningen.</p>
        <p>Fönstret räknas från vänster, sett inifrån rummet och utåt. Räkna inte om från gatan, för då byter F1 och F2 plats. En fönsterdörr som är den tredje öppningen från vänster är F3.</p>
        <p>I ett kopplat tvåluftsfönster är B1 vänster ytterbåge, B2 vänster innerbåge, B3 höger ytterbåge och B4 höger innerbåge. 2-KÖK-F1-B2 är den vänstra innerbågen i kökets första fönster på våning 2. I ett enluftsfönster stannar räkningen vid B1 och B2.</p>

        @include('partials.guide-number-table')

        <p>Rumskoderna hålls korta: KÖK, SOV1, SOV2, HALL, BAD, VARD. SOV1 och SOV2 räknas från trappan och medsols. En nytillverkad båge ärver numret för öppningen den ska sitta i. Skriv att den är nytillverkad, så att ingen letar efter det gamla träet.</p>

        <h2>Vanliga misstag</h2>
        <h3>Två bågar får samma nummer</h3>
        <p>Det händer när de ser likadana ut. De ska tillbaka till samma öppning, men de har varsitt skick och varsin plats i karmen.</p>

        <h3>Bara rummet står på lappen</h3>
        <p>Kök säger inte vilken öppning, och inte om bågen satt innerst eller ytterst. När färgen är på ser kökets bågar likadana ut. Skriv hela numret.</p>

        <h3>Märkningen sitter där verktyget går</h3>
        <p>Kittfals, en lös papperslapp eller en siffra i färg som ska skrapas bort är borta mitt i arbetet. Flytta inte märkningen utan att nästa person vet var den ska läsas.</p>

        <h3>Inner- och ytterbåge delar nummer</h3>
        <p>Då syns det inte vilken av dem som ska ut. Ytterbågen är den som tar väder. Den ska inte hamna innerst för att lappen bara sa kök, fönster 1.</p>

        <h3>Lappen och kortet glider isär</h3>
        <p>Någon skriver om numret på kortet men inte på brickan, eller tvärtom. Läs dem mot varandra när bågen lämnar verkstaden. Ett av dem ska inte vara en äldre version.</p>

        <h2>Lappen och QR-koden</h2>
        <p>Lappen säger vilket nummer bågen har, även med färg på händerna och utan täckning. QR-koden öppnar kortet, där plats, bilder, anteckningar och steg ligger. Den som inte minns om 2-KÖK-F1-B2 var vänster innerbåge skannar, i stället för att gissa.</p>
        <p>Koden ersätter inte lappen. En kod på en lapp som skrapas bort försvinner med numret. Fäst båda där de överlever arbetet. Kortet håller det som inte får plats på brickan.</p>

        @include('partials.guide-faq', ['faqs' => [
            [
                'question' => 'Varför inte bara skriva köket på lappen?',
                'answer' => 'Köket har ofta flera öppningar, och varje öppning har mer än en båge. När färgen är borta ser de likadana ut. Hela numret, med våning, rum, fönster och båge, är det som pekar ut en bestämd båge. Kök ensamt går inte att montera efter.',
            ],
            [
                'question' => 'Hur räknar jag fönstren i ett rum?',
                'answer' => 'Stå i rummet, se utåt och räkna öppningarna från vänster. Det första fönstret är F1. En fönsterdörr räknas med om den tas ur för sig. Håll samma håll i hela projektet. Räkna inte om från utsidan på fasaden, för då byter F1 och F2 plats.',
            ],
            [
                'question' => 'Får inner- och ytterbåge samma nummer?',
                'answer' => 'Nej. De är två bågar och ska tillbaka på var sitt ställe i karmen. I ett enluftsfönster är ytterbågen B1 och innerbågen B2. Skriv den andra bågens nummer i anteckningen om de ska transporteras som par.',
            ],
            [
                'question' => 'Var ska lappen sitta för att klara färg?',
                'answer' => 'På trä som inte skrapas rent och inte på kitt som byts. En papperslapp i kittfalsen försvinner. Använd en bricka eller en tejp som tål färg, på ett ställe ni alltid läser av, och skriv om numret om målningen täcker det. Det ska gå att läsa utan att skrapa i den färdiga färgen.',
            ],
            [
                'question' => 'Vad gör QR-koden som lappen inte gör?',
                'answer' => 'Lappen bär numret. Koden öppnar arbetskortet för just den bågen, med plats i klartext, bilder, anteckningar och steg. Använd båda. Koden hjälper inte om lappen är borta och bågarna ligger omärkta, och lappen rymmer inte skicket och glaset.',
            ],
            [
                'question' => 'Vad gör jag med en nytillverkad båge?',
                'answer' => 'Den ärver numret för öppningen och läget den ska sitta i. Återanvänd inte numret på en annan öppning, och hitta inte på ett nytt nummer vid sidan av schemat. Skriv på kortet att bågen är nytillverkad till det numret, så att ingen letar efter den gamla bågens trä.',
            ],
        ]])

        @include('partials.guide-cta')
    </div>
</main>
@endsection