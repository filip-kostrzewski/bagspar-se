@extends('layouts.app')

@section('title', 'Offert för fönsterrenovering — så skriver verkstaden den per båge — Bågspår')
@section('meta_description', 'Så bygger fönsterverkstaden en offert för renovering av fönsterbågar: en rad per båge, tydligt pris före rotavdrag, giltighetstid och kundens godkännande med namn via länk.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('guides.index') }}">← Alla guider</a>
    <p class="eyebrow">OFFERT</p>
    <h1 class="display-heading">Offert för fönsterrenovering</h1>
    <div class="guide-page__body body-copy">
        <p>En fönsterrenovering består av många små delar: några bågar ska lagas, andra bara målas, och ibland ska glaset bytas. En offert som bara säger en enda summa för "renovering av fönster" ger kunden lite att ta ställning till och verkstaden lite att luta sig mot efteråt. Den här guiden går igenom hur en offert kan byggas så att både kund och verkstad vet vad som är beställt.</p>

        <h2>Vad en offert bör göra tydligt</h2>
        <ul>
            <li>Vad som ingår, uppdelat så att kunden kan se vilka bågar och vilka rum priset gäller.</li>
            <li>Priset per rad och totalt.</li>
            <li>Hur länge offerten gäller.</li>
            <li>Hur rotavdraget hanteras, så att kunden inte tror att priset redan är sänkt.</li>
            <li>Att kunden har godkänt, med namn och tidpunkt.</li>
        </ul>

        <h2>En rad per båge</h2>
        <p>Skriv en rad för varje båge, med bågens nummer och rummet den sitter i, till exempel "Renovering av båge 2-KÖK-F1-B2 · Kök". Då ser kunden vad priset avser, och verkstaden kan senare jämföra offerten med arbetskorten per båge, som guiden om <a href="{{ route('guides.costing') }}">efterkalkyl för fönsterrenovering</a> visar. Behöver en båge något särskilt, som byte av glas eller en större trälagning, får den en egen rad eller en tydlig rad intill.</p>
        <p>Varje rad har en beskrivning, ett antal och ett à-pris, och summan räknas per rad och totalt. Använd antal när flera bågar får samma behandling till samma pris.</p>

        <h2>Rotavdraget hör till fakturan</h2>
        <p>Priset i offerten bör stå före eventuellt rotavdrag. Skatteverket anger att avdraget är högst 30 procent av arbetskostnaden inklusive moms och att material inte ger något avdrag, och att det är bara arbetad tid på plats hos kunden som ger rätt till avdrag. Det gäller alltså inte tiden i verkstaden. Läs mer i guiden om <a href="{{ route('guides.rot') }}">rotavdrag vid fönsterrenovering</a> eller direkt hos <a href="https://www.skatteverket.se/foretag/skatterochavdrag/rotochrut/safungerarrotavdraget.4.2ef18e6a125660db8b080002709.html" rel="noopener">Skatteverket</a>.</p>
        <p>Skriv gärna en mening i offerten om att avdraget görs på fakturan. Det undviker missförstånd om vad kunden ska betala.</p>

        <h2>Giltighetstid och inledning</h2>
        <p>En offert bör ha ett slutdatum, så att verkstaden inte är bunden av ett pris för alltid. Använd en kort inledning för sådant som gäller hela projektet, till exempel vad som är förutsatt eller när arbetet kan påbörjas. Rader och inledning ska gå att läsa utan förklaring.</p>

        <h2>Kundens godkännande</h2>
        <p>Ett godkännande är värt mest när det går att visa i efterhand. Skicka offerten som en länk, låt kunden läsa den och godkänna med sitt namn, och spara namnet och tidpunkten på offerten. När offerten är godkänd bör den inte kunna ändras, annars finns det två versioner att tvista om. I Bågspår är det så: en godkänd offert låses.</p>

        <h2>Mottagningsprotokollet kompletterar offerten</h2>
        <p>Offerten säger vad som ska göras. Skicket när bågarna hämtades visar <a href="{{ route('guides.intake-protocol') }}">mottagningsprotokollet</a>, med anmärkning och bild före per båge. Tillsammans går det att svara på både "vad kom vi överens om" och "hur såg bågen ut från början".</p>

        <h2>Offert i Bågspår</h2>
        <p>I Bågspår skriver ni offerten i projektet. Med ett klick fylls en rad per båge i, med bågens nummer och rum, och ni fyller i antal och à-pris. Ni kan lägga till en inledning och ett datum då offerten gäller till. Offerten mejlas som en länk till kunden, som godkänner genom att skriva sitt namn. Namnet och tidpunkten sparas på offerten, och en godkänd offert går inte att ändra. Ni kan också ladda ner den som PDF.</p>
        <p>På offerten står att priset gäller före eventuellt rotavdrag, som görs på fakturan. Bågspår räknar inte ut något rotavdrag.</p>

        <h2>Checklista före ni skickar</h2>
        <ul>
            <li>Finns det en rad för varje båge, med nummer och rum?</li>
            <li>Är antal och à-pris ifyllda, och stämmer summan?</li>
            <li>Står det tydligt att priset gäller före rotavdrag?</li>
            <li>Har offerten ett slutdatum?</li>
            <li>Har kunden en e-postadress i projektet, så att länken kan skickas?</li>
        </ul>

        <p>Vill ni skriva offerten per båge och ha den kopplad till arbetskorten? <a href="{{ route('register') }}">Prova Bågspår gratis</a> eller <a href="{{ route('contact') }}">kontakta oss</a>.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Offert för fönsterrenovering',
    'description' => 'Så bygger fönsterverkstaden en offert för renovering av fönsterbågar: en rad per båge, tydligt pris före rotavdrag, giltighetstid och kundens godkännande med namn via länk.',
    'pageUrl' => route('guides.quote'),
    'datePublished' => '2026-11-06',
])
