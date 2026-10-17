@extends('layouts.app')

@section('title', 'Allmänna villkor — Bågspår')
@section('meta_description', 'Villkor för Bågspår: kostnadsfri pilot i 3 månader utan automatisk betalning, därefter 790 kr per månad exklusive moms, utan bindningstid och utan uppsägningstid.')

@section('content')
<main id="main" class="privacy-page page-shell">
    <a class="text-link privacy-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">AVTAL</p>
    <h1 class="display-heading">Allmänna villkor</h1>
    <div class="privacy-page__body body-copy">
        <p>Här beskriver vi villkoren för verkstäder som provar eller använder Bågspår.</p>
        <p class="privacy-page__updated">Uppdaterad 17 oktober 2026 · Version 1.0.</p>
        <p>Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta.@if (filled(config('bagspar.contact_email'))) Kontakt: <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a>.@endif</p>

        <h2>1. Tjänsten och avtalet</h2>
        <p>Bågspår är en webbtjänst där en fönsterverkstad samlar märkning, bilder, anteckningar och arbetssteg för varje båge, och kan skriva ut en QR-etikett och en projektrapport.</p>
        <p>Tjänsten tillhandahålls av Prodi Digital AB. Kontaktuppgifter finns ovan.</p>
        <p>Villkoren gäller mellan Prodi Digital AB och den verkstad som skapar och använder kontot. Personen som skapar kontot ska ha rätt att företräda verkstaden.</p>

        <h2>2. Kostnadsfri pilot</h2>
        <p>Verkstaden får prova Bågspår kostnadsfritt i 3 månader. Ingen betalning krävs för att starta piloten.</p>
        <p>Ingen betalning dras automatiskt under piloten, och ingen betalning startar automatiskt när piloten tar slut. Vill verkstaden inte fortsätta behöver den inte säga upp en betalning som inte har börjat.</p>

        <h2>3. Pris efter piloten</h2>
        <p>Efter piloten: 790 kr/mån exkl. moms. Ingen bindningstid, säg upp när du vill.</p>
        <p>Priset gäller verkstadens konto. Moms enligt gällande regler tillkommer på 790 kr per månad.</p>

        <h2>4. Betalning</h2>
        <p>Ingen betalning dras automatiskt. En betald period börjar först när verkstaden väljer att fortsätta efter piloten.</p>
        <p>Prodi Digital AB bekräftar priset och hur betalningen sker innan något ska betalas. Att skapa ett konto eller att skicka en förfrågan är inte en betalning.</p>

        <h2>5. Bindningstid och uppsägning</h2>
        <p>Ingen bindningstid och ingen uppsägningstid. Verkstaden kan säga upp när den vill.</p>
        <p>Uppsägning görs genom att skriva till kontaktadressen ovan. Den gäller från den dag vi tar emot den, utan uppsägningstid. En period som redan är betald avslutas vid det slut som står i bekräftelsen. Vi drar ingen ny betalning efter uppsägningen.</p>

        <h2>6. Konto, innehåll och personuppgifter</h2>
        <p>Verkstadens ägare skapar kontot och kan bjuda in medarbetare. Medarbetare kan inte ändra verkstadens uppgifter eller ta bort projekt. Verkstaden ansvarar för inloggningarna och för att de uppgifter, namn och bilder som läggs in i tjänsten får användas där. Arbetskort, bilder och rapporter visas efter inloggning.</p>
        <p>Integritetspolicyn beskriver hur Prodi Digital AB hanterar kontouppgifter och uppgifter i verkstadens konto. För personuppgifter om verkstadens kunder ansvarar verkstaden för sin användning och för information till de registrerade. Om Prodi Digital AB behandlar sådana uppgifter för verkstadens räkning krävs ett separat personuppgiftsbiträdesavtal. Dessa villkor och integritetspolicyn är inte ett sådant avtal. Kontakta oss innan kontot används för sådan behandling.</p>

        <h2>7. Avslut och kontakt</h2>
        <p>Kontot avslutas när verkstaden ber om det via kontaktadressen ovan, eller när piloten har tagit slut och verkstaden inte har valt att fortsätta. När kontot avslutas tar vi bort kontouppgifter, projekt och bilder inom 30 dagar, om vi inte måste spara något enligt lag. Läs mer i integritetspolicyn.</p>
        <p>Om tjänsten inte fungerar som avsett kan du anmäla felet via kontaktadressen ovan. Vi undersöker felanmälan och arbetar för att åtgärda felet. Ingen särskild svarstid eller drifttid garanteras.</p>

        <h2>8. Tillämplig lag</h2>
        <p>Svensk lag gäller för dessa villkor. Tvister som inte kan lösas genom kontakt med oss prövas av svensk allmän domstol.</p>

        <p>Se även <a href="{{ route('privacy') }}">integritetspolicyn</a>.</p>
    </div>
</main>
@endsection
