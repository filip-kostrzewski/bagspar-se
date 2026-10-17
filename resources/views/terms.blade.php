@extends('layouts.app')

@section('title', 'Allmänna villkor — Bågspår')
@section('meta_description', 'Villkor för Bågspår: gratis provperiod utan kort, därefter abonnemang per verkstad som faktureras månadsvis eller årsvis, utan bindningstid.')

@section('content')
<main id="main" class="privacy-page page-shell">
    <a class="text-link privacy-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">AVTAL</p>
    <h1 class="display-heading">Allmänna villkor</h1>
    <div class="privacy-page__body body-copy">
        <p>Här beskriver vi villkoren för verkstäder som provar eller abonnerar på Bågspår.</p>
        <p class="privacy-page__updated">Uppdaterad 17 oktober 2026 · Version 2.0.</p>
        <p>Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta.@if (filled(config('bagspar.contact_email'))) Kontakt: <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a>.@endif</p>

        <h2>1. Tjänsten och avtalet</h2>
        <p>Bågspår är en webbtjänst där en fönsterverkstad samlar märkning, bilder, anteckningar och arbetssteg för varje båge, och kan skriva ut en QR-etikett och ladda ner eller skriva ut en projektrapport. Tjänsten kan också sammanställa verkstadens egna uppgifter om tid och material till ett ROT-underlag och låta verkstadens kund godkänna leveransen via en statuslänk. Verkstaden kan också skicka en offert som kunden godkänner via en länk, och dokumentera bågarnas skick vid mottagning i ett mottagningsprotokoll som kunden godkänner via statuslänken eller på plats; offerten och protokollet är verkstadens egna dokument. Underlaget är en sammanställning, inte skatterådgivning, och verkstaden ansvarar själv för sin fakturering och sitt avdrag.</p>
        <p>Tjänsten tillhandahålls av Prodi Digital AB. Kontaktuppgifter finns ovan.</p>
        <p>Villkoren gäller mellan Prodi Digital AB och den verkstad som skapar och använder kontot. Personen som skapar kontot ska ha rätt att företräda verkstaden.</p>

        <h2>2. Provperiod</h2>
        <p>En ny verkstad får prova Bågspår kostnadsfritt i {{ \App\Support\Pricing::trialDays() }} dagar från den dag kontot skapas. Inget kort och ingen betalning krävs, och ingen faktura skickas under provperioden.</p>
        <p>När provperioden tar slut låses kontot tills verkstaden beställer ett abonnemang. Projekt, bågar och bilder finns kvar, och verkstaden kan ladda ner sina uppgifter från kontot även medan det är låst. Ett konto som har varit låst i 90 dagar utan beställning kan vi ta bort tillsammans med dess innehåll.</p>

        <h2>3. Pris</h2>
        <p>Abonnemanget kostar {{ \App\Support\Pricing::monthlyLabel() }} exkl. moms per verkstad. {{ \App\Support\Pricing::seatsSentence() }} Antalet användare räknas som antalet konton i verkstaden på fakturadagen. Årsabonnemang kostar {{ \App\Support\Pricing::yearlyLabel() }} exkl. moms för de användare som ingår, plus {{ \App\Support\Pricing::kr(\App\Support\Pricing::extraSeatKr() * 12) }} kr/år för varje ytterligare användare.</p>
        <p>Moms enligt gällande regler tillkommer. Prisändringar meddelas via e-post minst 30 dagar innan de börjar gälla och gäller från nästa betalningsperiod. En årsperiod som redan är fakturerad påverkas inte.</p>

        <h2>4. Beställning och betalning</h2>
        <p>Verkstadens ägare beställer abonnemanget i appen under Abonnemang och väljer där månads- eller årsbetalning. Beställningen är bindande för den första betalningsperioden. Verkstaden anger organisationsnummer och en e-postadress som fakturan skickas till.</p>
        <p>Vi fakturerar i förskott: månadsabonnemang per månad och årsabonnemang per tolv månader. Fakturan skickas via e-post med {{ \App\Support\Pricing::invoiceDays() }} dagars betalningsvillkor. Vid sen betalning får vi ta ut dröjsmålsränta enligt räntelagen och lagstadgad påminnelseavgift, och vi får låsa kontot efter en påminnelse tills fakturan är betald. Inga kortuppgifter sparas hos oss.</p>

        <h2>5. Bindningstid och uppsägning</h2>
        <p>Ingen bindningstid utöver den betalningsperiod som redan är fakturerad. Abonnemanget förnyas med en ny period av samma längd tills det sägs upp.</p>
        <p>Uppsägning görs genom att skriva till kontaktadressen ovan. Ett månadsabonnemang upphör vid slutet av den betalda månaden. Ett årsabonnemang upphör vid slutet av den betalda tolvmånadersperioden om uppsägningen kommer in senast 30 dagar före periodens slut, annars efter nästa period. Redan betalda belopp återbetalas inte.</p>

        <h2>6. Konto, innehåll och personuppgifter</h2>
        <p>Verkstadens ägare skapar kontot och kan bjuda in medarbetare. Medarbetare kan inte ändra verkstadens uppgifter eller ta bort projekt. Verkstaden ansvarar för inloggningarna och för att de uppgifter, namn och bilder som läggs in i tjänsten får användas där. Arbetskort, bilder och rapporter visas efter inloggning.</p>
        <p>Integritetspolicyn beskriver hur Prodi Digital AB hanterar kontouppgifter och uppgifter i verkstadens konto. För personuppgifter om verkstadens kunder är verkstaden personuppgiftsansvarig och ansvarar för sin användning och för information till de registrerade. Prodi Digital AB är personuppgiftsbiträde. <a href="{{ route('dpa') }}">Personuppgiftsbiträdesavtalet</a> är en del av dessa villkor och godkänns tillsammans med dem.</p>

        <h2>7. Avslut och kontakt</h2>
        <p>Kontot avslutas när verkstaden ber om det via kontaktadressen ovan, när ett uppsagt abonnemang har löpt ut, eller enligt punkt 2 när en provperiod har tagit slut utan beställning. När kontot avslutas tar vi bort kontouppgifter, projekt och bilder inom 30 dagar, om vi inte måste spara något enligt lag. Fakturaunderlag sparas så länge bokföringslagen kräver. Läs mer i integritetspolicyn.</p>
        <p>Om tjänsten inte fungerar som avsett kan du anmäla felet via kontaktadressen ovan. Vi undersöker felanmälan och arbetar för att åtgärda felet. Ingen särskild svarstid eller drifttid garanteras.</p>

        <h2>8. Tillämplig lag</h2>
        <p>Svensk lag gäller för dessa villkor. Tvister som inte kan lösas genom kontakt med oss prövas av svensk allmän domstol.</p>

        <p>Se även <a href="{{ route('privacy') }}">integritetspolicyn</a> och <a href="{{ route('dpa') }}">personuppgiftsbiträdesavtalet</a>.</p>
    </div>
</main>
@endsection
