@extends('layouts.app')

@section('title', 'Arbetskort för fönsterbågar — Bågspår')
@section('meta_description', 'Arbetskort per fönsterbåge med plats, arbetssteg, anteckningar, bilder och en utskrivbar QR-etikett som öppnar kortet efter inloggning.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">ARBETSKORT</p>
    <h1 class="display-heading">Ett kort per båge</h1>
    <div class="guide-page__body body-copy">
        <p>Arbetskortet är bågens egen lapp, i mobilen. Den som tar emot bågen, den som skrapar och den som sätter tillbaka den läser samma kort. Inget flyttas över till en annan lista i slutet av dagen.</p>
        <p>Ett fönster kan ha fyra bågar. De ska inte dela ett kort. Ytterbåge och innerbåge skrapas, kittas och målas var för sig, och de ska tillbaka på var sitt ställe.</p>

        <h2>Så går bågen genom verkstaden</h2>
        <h3>1. Inlämnad</h3>
        <p>Kortet skapas när bågen tas emot. Numret skrivs likadant som på lappen. Plats, skick, glas och det som redan är bestämt förs in innan bågen ställs undan. Steget sätts till inlämnad, så att den som kommer till bänken ser att bågen är inne och att ingen har börjat skrapa den.</p>
        <p>Här hör <a href="{{ route('guides.documentation') }}">dokumentationen av fönsterrenoveringen</a> hemma: det som syntes vid öppningen, bilderna tagna innan färgen försvann, och beslutet om glaset. Saknas det på kortet när steget redan står på skrapning, är det svårt att återskapa.</p>

        <h3>2. Skrapning</h3>
        <p>Den som skrapar öppnar kortet, läser var färgen satt löst och var träet var mjukt, och flyttar steget till skrapning när arbetet börjar. Anteckna det som färgen dolde: ett mjukare hörn, ett stift som saknas, en spricka under kittet. Ska träet lagas innan kitt, lämna steget på skrapning och skriv vad som återstår.</p>

        <h3>3. Kittning</h3>
        <p>Innan kittet läggs läser du glasanteckningen: vilken ruta som ska ur, vilket glas som ska tillbaka, om stiften ska sparas. Steget flyttas till kittning när det arbetet pågår. Skriv om en ruta byttes, var originalrutan står, och om kittet fortfarande torkar så att ingen målar på det.</p>

        <h3>4. Målning</h3>
        <p>Den som målar läser kulören på kortet och ser att kittningen är gjord. Steget sätts till målning. Saknas kulör är det ett beslut som ska hämtas, inte en gissning utifrån grannbågen. Skriv vilken sida som är struken om bågen ska stå och torka mellan varven, och om inner- och yttersida har olika behandling.</p>
        <p>Numret på lappen ska gå att läsa även när färgen är på. Om målningen täcker märkningen, skriv numret på nytt innan bågen lämnar målarplatsen. Då stämmer <a href="{{ route('guides.marking') }}">märkningen av bågen</a> fortfarande med numret på kortet.</p>

        <h3>5. Klar</h3>
        <p>Klar betyder att bågen kan lämna verkstaden mot sin öppning. Inte att den redan sitter i väggen, och inte att den bara är grundmålad. Läs nummer, par, glas och beslag. Ytterbåge ut, innerbåge in. När den sitter i karmen skriver du det på samma kort.</p>

        <h2>Det som ligger på kortet</h2>
        <h3>Nummer och plats</h3>
        <p>Rubriken är bågens nummer, till exempel 2-KÖK-F1-B2, och platsen i klartext: våning 2, kök, vänster innerbåge. Efter inloggning ser den som öppnar kortet båda. Numret räcker när lappen sitter kvar. Klartexten räcker när någon läser listan i verkstaden utan att ha bågen framför sig.</p>

        <h3>QR-kod</h3>
        <p>Skriv ut en QR-etikett direkt från arbetskortet. När någon skannar den öppnas det privata arbetskortet för bågen. Skanning kräver inloggning till verkstadskontot. Etiketten visar också bågens nummer, så att den går att identifiera utan att skanna.</p>

        <h3>Anteckningar</h3>
        <p>Skriv korta rader: glaset tas ur, gångjärn saknas, kulör inte vald, kitt lagt så måla inte idag. Den som läser ska kunna handla på meningen utan era interna förkortningar.</p>

        <h3>Bilder</h3>
        <p>Bilderna från öppningen och från bänken ligger på samma kort som steget. En bild på sprickan hör till den båge sprickan sitter i.</p>

        <h2>Numret på kortet</h2>
        <p>Kortets nummer är samma som på lappen. Schemat är våning, rum, fönster och båge. 2-KÖK-F1-B2 betyder våning 2, köket, första fönstret från vänster sett inifrån och utåt, och den vänstra innerbågen.</p>
        <p>Bottenvåning är 1, nästa plan är 2 och källare är 0. Rummen skrivs korta: KÖK, SOV1, HALL. Fönstren räknas från vänster, bågarna utifrån och in. B1, B3 och B4 i samma öppning har egna kort.</p>

        @include('partials.guide-number-table')

        <p>Skriv hela numret i rubriken. B2 ensamt räcker inte när två rum ligger på samma bänk. I ett enluftsfönster är B1 ytterbåge och B2 innerbåge. Ett överljus som är en egen båge får nästa lediga B, och ordet överljus står i anteckningen.</p>

        <h2>Vanliga misstag</h2>
        <h3>Ett kort för hela fönstret</h3>
        <p>Då blandas skicket, och steget går inte att lita på. Den ena bågen kan vara kittad medan den andra skrapas. Ge varje båge ett kort.</p>

        <h3>Steget flyttas innan arbetet är gjort</h3>
        <p>Steget är det den som tar vid ska tro på. Sätt kittning när kittningen pågår, inte när någon hoppas att den blir gjord under dagen. Är träet inte färdigt för kitt, stanna på skrapning och skriv vad som återstår.</p>

        <h3>Anteckningen hamnar i en pärm</h3>
        <p>En rad i en pärm på kontoret syns inte vid bänken. Skriv där arbetet sker, på bågens kort. Pärmen kan finnas för annat. Kortet är det som följer med bågen.</p>

        <h3>Bilden visar inte vilken båge det är</h3>
        <p>En närbild på ett hörn utan nummer kan tillhöra vilken båge som helst från samma fasad. Lägg bilden på rätt kort direkt, eller se till att lappen syns i bild.</p>

        <h3>Klar används för tidigt</h3>
        <p>Klar är bågen som kan lämna verkstaden mot sin öppning. Grundstruket, kitt som fortfarande torkar eller en saknad skruv är inte klar. Skriv det som återstår och låt steget stå kvar på det arbete som pågår.</p>

        <h2>När täckningen är svag</h2>
        <p>Verkstadskontot kräver internetanslutning. Arbetskort och ändringar sparas på servern, så att verkstaden kan öppna samma uppgifter från sitt konto. Exemplet med testuppgifter är separat och går att prova utan täckning.</p>

        @include('partials.guide-faq', ['faqs' => [
            [
                'question' => 'Kan flera bågar dela ett arbetskort?',
                'answer' => 'Nej. Ett kort är en båge. Inner- och ytterbåge, och vänster och höger luft, har egna nummer och egna steg. De kan nämna varandra i anteckningen så att paret hålls ihop, men de ska inte stå på samma steg. Annars syns det inte vilken av dem som är skrapad och vilken som är klar.',
            ],
            [
                'question' => 'Vad betyder stegen på kortet?',
                'answer' => 'Inlämnad betyder att bågen är inne och ännu inte påbörjad. Skrapning, kittning och målning är det arbete som pågår. Klar betyder att bågen kan lämna verkstaden mot sin öppning. Flytta steget när arbetet börjar, och lämna en anteckning om något återstår innan nästa steg.',
            ],
            [
                'question' => 'Fungerar verkstadskontot utan internet?',
                'answer' => 'Nej. Verkstadskontot behöver internetanslutning för att öppna och uppdatera arbetskort. Uppgifterna sparas på servern. Exemplet med testuppgifter kan provas utan täckning.',
            ],
            [
                'question' => 'Ska bilden ligga på kortet eller i en egen mapp?',
                'answer' => 'På kortet. En mapp för hela projektet blandar hörn, sprickor och fasader från olika bågar. Bilden ska gå att öppna tillsammans med numret och steget. Ta den innan skrapning om den ska visa skadan, och se till att det går att se vilken båge det är.',
            ],
            [
                'question' => 'När ska steget stå på klar?',
                'answer' => 'När bågen är färdig att paras ihop med sin öppning: skrapad, kittad, målad, med glas och beslag enligt anteckningen, och med ett nummer som fortfarande går att läsa. Klar betyder inte att den redan är monterad i karmen. Skriv en rad till när den sitter på plats.',
            ],
        ]])

        @include('partials.guide-cta')
    </div>
</main>
@endsection
