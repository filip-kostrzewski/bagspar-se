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
        <p>Dokumentationen skrivs vid öppningen, innan kitt och färg skrapas bort och innan bågarna ligger i en bunt på golvet. Den som skrapar, den som kittar och den som monterar tillbaka ska kunna läsa samma uppgifter utan att ringa den som tog ur bågen.</p>

        <h2>Så skriver du ner bågen</h2>
        <h3>1. Stå kvar vid öppningen</h3>
        <p>Skriv plats, skick och bilder innan bågen lossas. Lägg den inte i bunten först. Bland andra bågar från samma rum syns det inte vilken öppning den satt i, eller om den satt innerst eller ytterst.</p>

        <h3>2. Skriv platsen så att en annan person hittar öppningen</h3>
        <p>Skriv projekt eller adress, våning, rum och vilken öppning det är, och om bågen är inner- eller ytterbåge. Använd samma nummer på lappen och på kortet. Kök ensamt räcker inte när rummet har flera öppningar. Lös spröjs hör till bågen. Rutorna beskrivs i anteckningen.</p>

        <h3>3. Beskriv skicket innan något skrapas</h3>
        <p>Titta på kitt, färg, trä och beslag medan de sitter som i väggen. Skriv det du ser: kitt som släppt i nederfalsen, färg som flagnar, mjukt trä i ett hörn, ett gångjärn som går trögt. Den raden styr lagning, beslag och hur djupt skrapningen ska gå. När färgen är borta syns det inte var den satt löst.</p>

        <h3>4. Notera glaset ruta för ruta</h3>
        <p>Räkna rutorna uppifrån och från vänster. Skriv om en ruta är sprucken, om glaset är gammalt, och om det ska ur eller sitta kvar. Exempel: nedre högra rutan tas ur, övriga sitter kvar, stiften sparas om de är hela. Märk glaset med bågens nummer om det tas ur.</p>

        <h3>5. Skriv material och det som ska bytas</h3>
        <p>Är kulör, kitt eller beslag bestämt, skriv det. Linoljekitt, eller att gångjärnet ska bytas, stannar annars hos den som var på plats. Skriv också om inget är bestämt. Kulör inte vald är bättre än ett tomt fält. Hoppa över en allmän arbetsbeskrivning.</p>

        <h3>6. Ta bilder innan bågen lossas</h3>
        <p>Ta en bild av öppningen och en närbild på skadan innan skrapning. Numret ska synas, eller så ska bilden ligga på den bågens kort. En gemensam mapp utan nummer går inte att använda sedan.</p>

        <h3>7. Sätt det första steget</h3>
        <p>När kortet är ifyllt sätter du steget som säger var bågen är. Inlämnad betyder att den är ur öppningen och inne i verkstaden. Nästa person ska se det utan att fråga. Steget, anteckningen och bilden hör ihop. Hur stegen sedan flyttas beskrivs på sidan om <a href="{{ route('guides.work-card') }}">arbetskort för fönsterbågar</a>.</p>

        <h3>8. Läs kortet när bågen ska tillbaka</h3>
        <p>Vid återmontering läser du plats, par och glas. Ytterbåge ut, innerbåge in, och en urtagen ruta tillbaka i samma fals. Jämför lapp och kort. Stämmer de inte, stanna. Det är <a href="{{ route('guides.marking') }}">märkningen av fönsterbågen</a> som gör att kortet går att lita på.</p>

        <h2>Numret som binder ihop anteckningen</h2>
        <p>Anteckningen behöver ett nummer som är detsamma på lappen och på kortet. Schemat är våning, rum, fönster och båge. 2-KÖK-F1-B2 är ett sådant nummer, inte ett namn på en kund och inte ett löpnummer som börjar om i varje rum.</p>
        <p>2 är våningen: bottenvåning 1, nästa plan 2, källare 0. KÖK är rummet utan mellanslag. Två sovrum blir SOV1 och SOV2, från trappan och medsols. F1 räknas från vänster när du står i rummet och ser ut. En fönsterdörr är en egen öppning. Ett överljus som tas ur för sig får nästa B-nummer i samma fönster.</p>
        <p>B2 är bågen. Räkna utifrån och in, från vänster till höger. I ett kopplat tvåluftsfönster är B1 vänster ytterbåge, B2 vänster innerbåge, B3 höger ytterbåge och B4 höger innerbåge. 2-KÖK-F1-B2 är alltså vänster innerbåge i kökets första fönster, på våning 2.</p>

        @include('partials.guide-number-table')

        <p>Skriv hela numret på lappen, i kortets rubrik och i anteckningen. Förkorta inte till B2 när flera rum är inne i verkstaden samtidigt. B2 finns i varje fönster. I ett enluftsfönster finns bara B1 och B2: ytterbåge och innerbåge. Samma räknesätt, färre bågar.</p>

        <h2>Vanliga misstag</h2>
        <h3>Bara rummet skrivs</h3>
        <p>Kök räcker inte. Köket har ofta fler än en öppning, och inner- och ytterbåge ska tillbaka som par. Utan fönsternummer och bågnummer blir bunten ett pussel den dag färgen är på och bågarna ser likadana ut.</p>

        <h3>Skicket skrivs efter skrapningen</h3>
        <p>Då är ytan redan borta. Skriv kitt, färg, trä och beslag vid öppningen. Komplettera vid bänken om du hittar mer, men ersätt inte den första anteckningen. Båda raderna behövs: hur det såg ut i huset, och vad som syntes när färgen var borta.</p>

        <h3>Glaset noteras för hela fönstret på en gång</h3>
        <p>Sprickan sitter i en ruta på en båge. Skriv båge och ruta. Annars kan glaset tas ur på fel båge, eller så blandas originalglas från två öppningar i samma back.</p>

        <h3>Bilderna ligger för sig</h3>
        <p>En bild som inte sitter på bågens kort, och som inte visar numret, går inte att använda när någon annan tar vid. Lägg bilden på kortet för den båge den visar.</p>

        <h3>Inner- och ytterbåge delar anteckning</h3>
        <p>De lämnar öppningen tillsammans, men ytterbågen tar väder och innerbågen tar rummet. Ge dem varsitt kort och varsitt nummer, och skriv parets nummer på båda.</p>

        @include('partials.guide-faq', ['faqs' => [
            [
                'question' => 'Räcker det att fotografera bågen?',
                'answer' => 'Nej. Bilden visar hur det såg ut, men den säger inte vilken öppning bågen ska tillbaka till om numret saknas, och den säger inte om glaset ska tas ur. Skriv plats, skick och beslut på kortet, och låt bilden ligga där som stöd. En bild utan nummer är svår att koppla till rätt båge när flera från samma rum ligger på bänken.',
            ],
            [
                'question' => 'Ska inner- och ytterbåge dokumenteras var för sig?',
                'answer' => 'Ja. De sitter i samma öppning men de slits olika, och de ska tillbaka på var sitt ställe. Ge dem varsitt nummer enligt schemat och varsitt kort. Skriv på båda vilket nummer den andra bågen har, så att paret går att hålla ihop utan att blandas ihop.',
            ],
            [
                'question' => 'Vad gör jag om skadan syns först vid skrapningen?',
                'answer' => 'Skriv det på samma båges kort, och skriv att det syntes först när färgen var borta. Ta en bild då. Ta inte bort anteckningen från öppningen. Den förklarar hur bågen såg ut i huset, och den nya raden förklarar vad bänken visade.',
            ],
            [
                'question' => 'Hur noga ska glaset beskrivas?',
                'answer' => 'Så noga att en annan person kan peka på rätt ruta. Räkna uppifrån och från vänster, skriv om rutan är sprucken, om glaset är gammalt och ska sparas, och om det ska ur eller sitta kvar. En rad som bara säger att glaset är dåligt går inte att arbeta efter.',
            ],
            [
                'question' => 'Vem ska kunna läsa det som skrivs?',
                'answer' => 'Den som inte var med vid demonteringen. Skriv rum, nummer och skada i klartext, utan förkortningar som bara du använder. Kulör och kitt ska gå att läsa av någon som tar vid nästa dag, eller av den som monterar tillbaka bågen flera veckor senare.',
            ],
        ]])

        @include('partials.guide-cta')
    </div>
</main>
@endsection
