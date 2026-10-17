@extends('layouts.app')

@section('title', 'Dokumentera en fönsterrenovering — Bågspår')
@section('meta_description', 'Så samlar du märkning, bilder före och efter, arbetssteg och en projektrapport per båge. Rutinen lovar inte hur arbetet blir.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('guides.index') }}">← Alla guider</a>
    <p class="eyebrow">DOKUMENTATION</p>
    <h1 class="display-heading">Dokumentera fönsterrenoveringen</h1>
    <div class="guide-page__body body-copy">
        <p>När en fönsterrenovering lämnar verkstaden ska någon kunna se vilken båge som hör till vilken öppning, hur den såg ut innan arbetet och vilket steg den står på. Det gäller den som monterar tillbaka bågen, och det gäller den som tar emot en rapport: kunden eller en bostadsrättsförening. Dokumentationen är underlaget. Den säger inte hur resultatet blir.</p>
        <p>Börja vid öppningen, innan bågen lossas. Skriv märkningen, ta bilderna och sätt det första steget medan träet fortfarande sitter där det hör hemma. Samma uppgifter följer sedan med bågen genom verkstaden och kan samlas i en projektrapport när arbetet ska visas.</p>

        <h2>Märk bågen så att öppningen går att hitta</h2>
        <p>Märkningen binder ihop lappen på träet, bilderna och rapporten. Utan ett nummer som är detsamma på bågen och på kortet går det inte att veta vilken bild som hör till vilken öppning när flera bågar från samma rum ligger på bänken.</p>
        <p>Använd ett nummer som rymmer våning, rum, fönster och båge. 2-KÖK-F1-B2 är ett sådant nummer: våning 2, köket, första fönstret från vänster sett inifrån och utåt, och den vänstra innerbågen. I ett kopplat tvåluftsfönster är B1 vänster ytterbåge, B2 vänster innerbåge, B3 höger ytterbåge och B4 höger innerbåge. I ett enluftsfönster stannar räkningen vid B1 och B2.</p>
        <p>Skriv hela numret, inte bara B2. När två rum är inne i verkstaden finns det en B2 i varje fönster. 2-KÖK-F1-B2 och 2-SOV1-F1-B2 är olika bågar. Skriv samma tecken på lappen och på arbetskortet innan bågen ställs undan. Hur lappen fästs så att den överlever skrapning och färg står i guiden om <a href="{{ route('guides.marking') }}">märkning av fönsterbågar</a>.</p>
        <p>Ge inner- och ytterbåge varsitt nummer och skriv parets nummer i anteckningen. Märk en ruta som tas ur med samma bågnummer. Bestäm räkningen innan första bågen lossas, håll samma håll i hela projektet och skriv det i projektets anteckning.</p>

        <h2>Bilder före och efter, per båge</h2>
        <p>En bild visar hur det såg ut. Den ersätter inte numret, och den säger inte vilken ruta som ska tas ur om det inte står i anteckningen. Bilden hör hemma på den båge den visar, inte i en gemensam mapp för hela huset.</p>
        <p>Ta före-bilden vid öppningen, innan skrapning. Ta en bild av öppningen så att rummet går att känna igen, och en närbild på det som anteckningen nämner: kitt som släppt, färg som flagnar, mjukt trä, en sprucken ruta. Lappen eller numret ska synas i bild, eller så ska bilden läggas direkt på den bågens kort. En närbild på ett hörn utan nummer kan tillhöra vilken båge som helst från samma fasad.</p>
        <p>Ta efter-bilden när steget den ska visa är gjort. Inte innan. Efter skrapning visar bilden det trä som färgen dolde. Efter glasning visar den rutan som sattes tillbaka och kittet som fortfarande kan torka. Efter målning visar den ytan som är struken. Skriv i anteckningen vilket moment bilden hör till, så att före och efter inte blandas när rapporten läses.</p>
        <p>Håll bilderna per båge. Inner- och ytterbåge får egna bilder. Lägg upp bilden på rätt kort samma dag. En bild utan nummer går inte att koppla till en öppning, och det är inte ett omdöme om arbetet.</p>

        <h2>Arbetsstegen hör till dokumentationen</h2>
        <p>Steget säger var bågen är i arbetet. Det är en del av dokumentationen, inte ett besked om att momentet blir färdigt en viss dag. Sätt steget när arbetet börjar, och skriv vad som återstår om det inte är klart.</p>
        <p>I Bågspår är stegen Mottagen, Skrapning, Trälagning, Glasning, Målning och Klar. Mottagen betyder att bågen är inne och att plats, skick och de första bilderna är på kortet. Skrapning, trälagning, glasning och målning är de moment som pågår. Klar betyder att bågen kan lämna verkstaden mot sin öppning. Det betyder inte att den sitter i väggen, och inte att någon har godkänt ytan.</p>
        <p>Flytta inte steget i förväg. Är kittet fortfarande mjukt är bågen inte klar för målning. Skriv vad som återstår. Den som tar vid ska kunna lita på steget.</p>
        <p>Anteckningen och steget hör ihop. En rad som säger att nedre högra rutan tas ur, att övriga rutor sitter kvar och att kulören inte är vald, är underlag. Den är inte ett besked om hur glaset blir. Hur stegen används på kortet beskrivs på sidan om <a href="{{ route('guides.work-card') }}">arbetskort för fönsterbågar</a>. Det som ska skrivas vid öppningen, innan något skrapas, beskrivs i guiden om <a href="{{ route('guides.documentation') }}">dokumentation vid fönsterrenovering</a>.</p>

        <h2>Projektrapport till kunden eller föreningen</h2>
        <p>När bågarna ska visas samlas korten i en projektrapport. Rapporten är en utskrift av det som står på korten: projektets namn, plats, antal bågar, anteckningar, varje båges nummer, rum, steg och de bilder som ligger på kortet. I Bågspår skrivs rapporten ut eller sparas som PDF i webbläsaren.</p>
        <p>Rapporten kan lämnas till kunden eller till en bostadsrättsförening som underlag för vad som är gjort på vilka bågar. Den visar de uppgifter verkstaden har skrivit in. Den lovar inte ett resultat, en kulör som ännu inte är vald, eller att en förening godkänner arbetet. Saknas en uppgift på kortet saknas den också i rapporten. Fyll i kortet innan rapporten skrivs ut, inte efteråt i ett annat dokument.</p>
        <p>Läs rapporten mot bågarna innan den lämnas vidare. Numret på lappen och i rapporten ska vara detsamma, och före- och efterbild ska ligga på den båge de visar. Stämmer det inte, rätta kortet och skriv ut igen. Skriv vilka våningar rapporten avser. En mening om att kulör inte är vald är tydligare än ett omdöme om arbetet.</p>

        <h2>Vanliga misstag</h2>
        <h3>Bilderna ligger i en mapp för hela huset</h3>
        <p>Då går det inte att se vilken båge som är före och vilken som är efter när färgen är på och bågarna ser likadana ut. Lägg varje bild på bågens kort.</p>

        <h3>Bara rummet står i rapporten</h3>
        <p>Kök räcker inte när rummet har flera öppningar. Hela numret ska med, så att föreningen eller kunden kan peka på en bestämd båge.</p>

        <h3>Efter-bilden tas innan momentet är gjort</h3>
        <p>Då visar rapporten en yta som fortfarande skulle skrapas eller kittas. Ta bilden när steget den ska visa är gjort, och skriv vilket moment det är.</p>

        <h3>Steget Klar används för tidigt</h3>
        <p>Klar är bågen som kan lämna verkstaden. Kitt som torkar eller en saknad skruv är inte klar. Låt steget stå kvar och skriv vad som återstår.</p>

        <h3>Rapporten skrivs om i efterhand</h3>
        <p>Om numret rättas i ett textdokument men inte på kortet och på lappen glider underlaget isär. Rätta kortet, så att nästa utskrift visar samma nummer som bågen.</p>

        @include('partials.guide-faq', ['faqs' => [
            [
                'question' => 'Räcker en gemensam bildmapp?',
                'answer' => 'Nej. Lägg bilden på den båge den visar, med före och efter åtskilda.',
            ],
            [
                'question' => 'Vad ska rapporten till en förening innehålla?',
                'answer' => 'Numret, platsen, steget, anteckningen och bilderna på varje båges kort. Det är underlag, inte ett omdöme om arbetet.',
            ],
            [
                'question' => 'När tar jag efter-bilden?',
                'answer' => 'När momentet bilden ska visa är gjort. Skriv om det är efter skrapning, glasning eller målning.',
            ],
            [
                'question' => 'Är steget Klar ett godkännande?',
                'answer' => 'Nej. Klar betyder att bågen kan lämna verkstaden mot sin öppning, inte att ytan är godkänd.',
            ],
        ]])

        @include('partials.guide-cta')
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Dokumentera fönsterrenoveringen',
    'description' => 'Så samlar du märkning, bilder före och efter, arbetssteg och en projektrapport per båge. Rutinen lovar inte hur arbetet blir.',
    'pageUrl' => route('guides.document-renovation'),
])
