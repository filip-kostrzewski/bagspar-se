@extends('layouts.app')

@section('title', 'Fönsterrenovering i kulturhistoriskt värdefulla hus — varsamhet och dokumentation — Bågspår')
@section('meta_description', 'Vad varsamhetskravet och förvanskningsförbudet i PBL betyder när fönsterverkstaden renoverar bågar i ett äldre eller kulturhistoriskt värdefullt hus, och hur arbetet dokumenteras per båge.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('guides.index') }}">← Alla guider</a>
    <p class="eyebrow">KULTURVÄRDEN</p>
    <h1 class="display-heading">Fönsterrenovering i kulturhistoriskt värdefulla hus</h1>
    <div class="guide-page__body body-copy">
        <p>Många av de fönster som skickas till en fönsterverkstad sitter i hus som är äldre än hundra år. Där räcker det inte att bågen blir tät och snygg. Plan- och bygglagen ställer krav på hur ändringar görs, och kunden, en bostadsrättsförening eller kommunen kan vilja veta vad som faktiskt gjordes med bågarna. Den här guiden går igenom vad reglerna säger och hur verkstaden kan dokumentera arbetet så att det går att visa i efterhand.</p>

        <h2>Varsamhetskravet gäller alla hus</h2>
        <p>Enligt 8 kap. 17 § plan- och bygglagen ska en ändring av en byggnad utföras varsamt, så att man tar hänsyn till byggnadens karaktärsdrag och tar till vara dess tekniska, historiska, kulturhistoriska, miljömässiga och konstnärliga värden. Boverket skriver att kravet gäller alla byggnader och alla ändringar, invändigt som utvändigt, oberoende av om åtgärden kräver bygglov eller inte.</p>
        <p>I Boverkets föreskrifter (BFS 2024:8, 12 kap. 4 §) står vad som ska vägas in: proportioner, form och volym, materialval och utförande, färgsättning samt detaljomsorg och detaljeringsnivå. För ett fönster är det sådant som spröjs, profiler, glas, beslag och kulör.</p>

        <h2>Förvanskningsförbudet för särskilt värdefulla byggnader</h2>
        <p>En byggnad som är särskilt värdefull från historisk, kulturhistorisk, miljömässig eller konstnärlig synpunkt får inte förvanskas (8 kap. 13 § PBL). Boverket påpekar att förbudet inte är ett förändringsförbud: det är de egenskaper som gör byggnaden värdefull som är skyddade. Enligt BFS 2024:8, 12 kap. 5 §, ska en åtgärd inte förändra byggnadens karaktärsdrag, inte skada de egenskaper som ligger till grund för kulturvärdena, och vid utbyte av byggnadsdelar utföras med material och hantverksteknik som är anpassad till byggnadens ålder och karaktär.</p>
        <p>Det är byggnadens värden som avgör om förbudet gäller, inte om huset finns med i en lista. Boverkets föreskrifter (12 kap. 6 §) säger också att byggnader från tiden före 1920-talets bebyggelseexpansion, som har sin huvudsakliga karaktär bevarad, ska anses vara särskilt värdefulla om inte något talar däremot. Kommunens översiktsplan, detaljplan, områdesbestämmelser eller kulturmiljöprogram kan ge besked om hur kommunen ser på ett visst hus.</p>
        <p>Läs mer hos Boverket om <a href="https://www.boverket.se/sv/PBL-kunskapsbanken/teman/kulturvarden/kulturvarden-i-plan---och-bygglagen/krav-pa-byggnadsverk-och-tomter/varsamhetskravet/" rel="noopener">varsamhetskravet</a> och <a href="https://www.boverket.se/sv/PBL-kunskapsbanken/teman/kulturvarden/kulturvarden-i-plan---och-bygglagen/krav-pa-byggnadsverk-och-tomter/forvanskningsforbudet/" rel="noopener">förvanskningsförbudet</a>.</p>

        <h2>Vad det betyder i verkstaden</h2>
        <p>Om ett fönsterbyte kräver bygglov är en fråga för kommunen, inte för verkstaden. Men det är verkstaden som ser bågen närmast, och det är där besluten om vad som lagas, byts och sparas fattas. Boverket redovisar till exempel ett avgörande där domstolen inte ansåg det varsamt att byta spröjsade fönster mot fönster utan spröjs på ett radhus från 1970-talet i en kulturhistoriskt värdefull miljö (MÖD 2015-06-08, mål P 169-15).</p>
        <p>Några frågor som är bra att ha svar på innan arbetet börjar:</p>
        <ul>
            <li>Vet kunden om huset är utpekat som kulturhistoriskt värdefullt, eller om det finns skyddsbestämmelser i detaljplanen?</li>
            <li>Vilka delar ska vara kvar: originalglas, spröjs, beslag, profiler?</li>
            <li>Vilken färgtyp och kulör är beställd, och vem har bestämt det?</li>
            <li>Vad gör verkstaden om en del inte går att laga och måste ersättas?</li>
        </ul>

        <h2>Dokumentera per båge</h2>
        <p>När en båge är en del av ett värdefullt hus är det värt att kunna visa vad den var före arbetet, vad som gjordes och vad som byttes. Det gäller varje båge för sig, inte bara projektet som helhet.</p>
        <ul>
            <li>Skick vid mottagningen, med bild före skrapning. Se guiden om <a href="{{ route('guides.intake-protocol') }}">mottagningsprotokollet</a>.</li>
            <li>Vad som lagades och vad som byttes ut, och med vilket material.</li>
            <li>Vilket glas som sitter kvar och vilket som är nytt.</li>
            <li>Vilken färg och kulör som användes.</li>
            <li>Bilder från bänken, till exempel efter skrapning och efter trälagning.</li>
        </ul>
        <p>Med en märkning som följer bågen, som 2-KÖK-F1-B2, går uppgifterna att koppla till rätt öppning när bågen sätts tillbaka. Hur ni lägger upp dokumentationen beskrivs mer i guiden om att <a href="{{ route('guides.document-renovation') }}">dokumentera fönsterrenovering</a>.</p>

        <h2>Rapporten till kunden eller föreningen</h2>
        <p>En sammanställning efter jobbet gör det lättare för fastighetsägaren att svara på frågor från kommunen eller nästa styrelse. Den kan visa varje båge med plats, åtgärder, material och bilder före och efter. För bostadsrättsföreningar finns mer i guiden om <a href="{{ route('guides.brf-report') }}">rapport till BRF</a>.</p>

        <h2>Dokumentation i Bågspår</h2>
        <p>I Bågspår får varje båge ett eget arbetskort med plats, anteckningar, tid och material. Ni lägger upp till 20 bilder per båge och flyttar kortet genom stegen mottagen, skrapning, trälagning, glasning, målning och klar. Mottagningsprotokollet sparar skick och bilder när bågarna hämtas, och projektrapporten kan laddas ner som PDF eller skrivas ut.</p>
        <p>Bågspår bedömer inte om ett hus är kulturhistoriskt värdefullt och ger inga juridiska råd. Det ni skriver och fotograferar på arbetskorten är verkstadens egen dokumentation.</p>

        <h2>Checklista före första bågen lossas</h2>
        <ul>
            <li>Har kunden berättat om huset har skyddsbestämmelser eller är utpekat som värdefullt?</li>
            <li>Är det bestämt vad som ska sparas: glas, spröjs, beslag?</li>
            <li>Är färgtyp och kulör bestämda och skrivna?</li>
            <li>Finns det en bild av varje båge innan skrapning?</li>
            <li>Är det skrivet vad som byttes och varför?</li>
        </ul>

        <p>Vill ni ha skick, material och bilder samlade per båge? <a href="{{ route('register') }}">Prova Bågspår gratis</a> eller <a href="{{ route('contact') }}">kontakta oss</a>.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Fönsterrenovering i kulturhistoriskt värdefulla hus',
    'description' => 'Vad varsamhetskravet och förvanskningsförbudet i PBL betyder när fönsterverkstaden renoverar bågar i ett äldre eller kulturhistoriskt värdefullt hus, och hur arbetet dokumenteras per båge.',
    'pageUrl' => route('guides.heritage'),
    'datePublished' => '2026-11-09',
])
