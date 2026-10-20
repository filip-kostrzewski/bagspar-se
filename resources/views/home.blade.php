@extends('layouts.app')

@section('title', 'Bågspår — arbetskort för fönsterrenovering')
@section('meta_description', 'Arbetskort för fönsterverkstäder. Håll ihop märkning, bilder och arbetssteg för varje båge.')

@section('content')
@php
    /** @var list<array{question: string, answer: string}> $homeFaqs */
    $homeFaqs = [
        [
            'question' => 'Hur kommer jag igång?',
            'answer' => 'Skapa ett verkstadskonto. De första '.\App\Support\Pricing::trialDays().' dagarna är gratis och kräver inget kort. Ägaren kan bjuda in medarbetare till samma verkstad.',
        ],
        [
            'question' => 'Vad kostar Bågspår?',
            'answer' => \App\Support\Pricing::monthlyLabel().' exkl. moms per verkstad. '.\App\Support\Pricing::seatsSentence().' '.\App\Support\Pricing::yearlySentence().' '.\App\Support\Pricing::invoiceSentence(),
        ],
        [
            'question' => 'Hjälper Bågspår med ROT?',
            'answer' => 'Ja. Tid och material som skrivs på arbetskorten summeras per projekt till ett ROT-underlag som du laddar ner som PDF eller CSV. Själva avdraget räknar du som vanligt på fakturan; underlaget är en sammanställning av verkstadens egna uppgifter, inte skatterådgivning.',
        ],
        [
            'question' => 'Kan jag skicka offerter?',
            'answer' => 'Ja. Du skriver offerten i projektet och mejlar en länk till kunden, som godkänner med sitt namn. Bågspår räknar inte ut något ROT-avdrag; det görs på fakturan.',
        ],
        [
            'question' => 'Vad händer när provperioden tar slut?',
            'answer' => 'Kontot låses tills ägaren beställer abonnemanget i appen. Projekt, bågar och bilder finns kvar, och verkstadens data kan alltid laddas ner. Ingen faktura skickas utan en beställning.',
        ],
        [
            'question' => 'Kan jag prova arbetskortet först?',
            'answer' => 'Ja. Ett exempel med testuppgifter är öppet utan konto. Ändringar där påverkar inte verkstadens uppgifter.',
        ],
        [
            'question' => 'Fungerar Bågspår utan internet?',
            'answer' => 'Verkstadskontot behöver internetanslutning. Bara exemplet med testuppgifter kan provas utan internet.',
        ],
    ];

    if (\App\Mail\OutboundMail::isConfigured()) {
        $homeFaqs[] = [
            'question' => 'Får jag en bekräftelse på min förfrågan?',
            'answer' => 'Du får en bekräftelse via e-post.',
        ];
    }

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(
            fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ],
            $homeFaqs,
        ),
    ];
@endphp

<main id="main" class="home-page">
    <section class="home-hero page-shell" aria-labelledby="hero-title">
        <div class="home-hero__copy" data-reveal>
            <p class="eyebrow">FÖR FÖNSTERRENOVERARE</p>
            <h1 id="hero-title" class="display-heading">Varje båge. Rätt arbetskort.</h1>
            <p class="home-hero__intro">Håll ihop märkning, bilder och arbetssteg från verkstad till återmontering.</p>
            <div class="home-hero__actions">
                <a class="button" href="{{ route('register') }}">{{ \App\Support\Pricing::trialHeadline() }}</a>
                <a class="text-link home-hero__demo-link" href="{{ route('demo') }}">
                    Prova exempel
                    @include('partials.icon', ['name' => 'arrow-right'])
                </a>
            </div>
            <p class="home-hero__offer">Inget kort behövs <span aria-hidden="true">·</span> {{ \App\Support\Pricing::offerSentence() }}</p>
            <ul class="hero-benefits" aria-label="Det här samlar arbetskortet">
                <li>
                    @include('partials.icon', ['name' => 'tag'])
                    <span>Märk varje båge</span>
                </li>
                <li>
                    @include('partials.icon', ['name' => 'image'])
                    <span>Samla bilder</span>
                </li>
                <li>
                    @include('partials.icon', ['name' => 'check'])
                    <span>Följ arbetsstegen</span>
                </li>
            </ul>
        </div>

        <figure class="home-hero__visual" data-reveal>
            <span class="home-hero__grid" aria-hidden="true"></span>
            <img
                class="home-hero__photo"
                src="{{ asset('images/workshop-hero.webp') }}"
                srcset="{{ asset('images/workshop-hero-small.webp') }} 640w, {{ asset('images/workshop-hero.webp') }} 1122w"
                sizes="(max-width: 720px) calc(100vw - 40px), (max-width: 1200px) 48vw, 620px"
                alt="En äldre träbåge står på arbetsbänken i en fönsterverkstad"
                width="1122"
                height="1402"
                fetchpriority="high"
                decoding="async"
            >
            <span class="brass-tag" aria-label="Exempel på fysisk märkning">2-KÖK-F1-B2</span>
            <article class="home-example-card" aria-label="Exempel på arbetskort för båge 2-KÖK-F1-B2">
                <div class="home-example-card__topline">
                    <p>Exempel på arbetskort</p>
                    <span class="home-example-card__status">Mottagen</span>
                </div>
                <div class="home-example-card__main">
                    <div>
                        <h2>2-KÖK-F1-B2</h2>
                        <p>Våning 2 <span aria-hidden="true">·</span> Kök</p>
                    </div>
                    <span class="home-example-card__mark" aria-hidden="true">
                        @include('partials.icon', ['name' => 'check'])
                    </span>
                </div>
            </article>
            <figcaption class="home-hero__caption">En märkning som följer bågen.</figcaption>
        </figure>
    </section>

    <section class="workshop-story section-space page-shell" aria-labelledby="story-title">
        <div class="workshop-story__intro" data-reveal>
            <p class="eyebrow">EN PLATS FÖR HELA ARBETET</p>
            <h2 id="story-title" class="section-heading">Låt hantverket ta plats. Inte letandet.</h2>
            <p class="body-copy">Varje båge, bild och anteckning hör ihop på samma arbetskort.</p>
        </div>
        <div class="workshop-story__note" data-reveal>
            <span class="workshop-story__line" aria-hidden="true"></span>
            <p>Se stegen och platsen direkt, så att nästa moment börjar med rätt båge.</p>
        </div>
    </section>

    <section id="arbetsflode" class="atelier" aria-labelledby="atelier-title" data-workflow>
        <div class="atelier__inner page-shell">
            <div class="atelier__intro" data-reveal>
                <p class="eyebrow eyebrow--light">FRÅN VERKSTADEN</p>
                <h2 id="atelier-title" class="section-heading section-heading--light">Ett arbetskort. Hela vägen.</h2>
                <p class="atelier__lead">Välj ett steg och se hur samma arbetskort följer bågen.</p>

                @php
                    $workflowSteps = [
                        ['key' => 'received', 'number' => '01', 'label' => 'Mottagen', 'description' => 'Bågen är inne. Skriv rum, placering och en första anteckning på arbetskortet.', 'image' => 'workshop-hero', 'smallWidth' => 640, 'width' => 1122, 'height' => 1402, 'alt' => 'En äldre träbåge står på arbetsbänken redo att märkas'],
                        ['key' => 'scraping', 'number' => '02', 'label' => 'Skrapning', 'description' => 'Anteckna vad som skrapas och lägg bilder medan färgen går av.', 'image' => 'workshop-scraping', 'smallWidth' => 600, 'width' => 1200, 'height' => 800, 'alt' => 'Färg skrapas av en äldre träbåge', 'version' => substr(hash_file('sha256', public_path('images/workshop-scraping.webp')), 0, 12)],
                        ['key' => 'repair', 'number' => '03', 'label' => 'Trälagning', 'description' => 'Skriv vilka delar som lagas, så nästa person ser vad som redan är gjort.', 'image' => 'workshop-repair', 'smallWidth' => 600, 'width' => 1200, 'height' => 800, 'alt' => 'Trä lagas i en fönsterbåge'],
                        ['key' => 'glazing', 'number' => '04', 'label' => 'Glasning', 'description' => 'Håll koll på kitt, stift och vilka rutor som sitter kvar.', 'image' => 'workshop-glazing', 'smallWidth' => 600, 'width' => 1200, 'height' => 800, 'alt' => 'Kitt stryks mot glaset i en äldre träbåge', 'version' => substr(hash_file('sha256', public_path('images/workshop-glazing.webp')), 0, 12)],
                        ['key' => 'painting', 'number' => '05', 'label' => 'Målning', 'description' => 'Anteckna färg och skikt medan bågen målas.', 'image' => 'workshop-painting', 'smallWidth' => 600, 'width' => 1200, 'height' => 800, 'alt' => 'En träbåge målas med pensel'],
                        ['key' => 'ready', 'number' => '06', 'label' => 'Klar', 'description' => 'Bågen är klar i verkstaden. Rapporten samlar nummer, rum, steg och bilder.', 'image' => 'workshop-restored', 'smallWidth' => 600, 'width' => 1200, 'height' => 800, 'alt' => 'En färdigrenoverad träbåge i dagsljus'],
                    ];
                @endphp
                <div class="atelier-steps" role="group" aria-label="Välj arbetssteg" data-workflow-controls hidden>
                    @foreach ($workflowSteps as $workflowStep)
                        <button
                            class="atelier-step @if ($loop->first) is-active @endif"
                            type="button"
                            data-workflow-step
                            data-step="{{ $workflowStep['key'] }}"
                            data-description="{{ $workflowStep['description'] }}"
                            data-status="{{ $workflowStep['label'] }}"
                            data-image="{{ asset('images/'.$workflowStep['image'].'.webp') }}{{ isset($workflowStep['version']) ? '?v='.$workflowStep['version'] : '' }}"
                            data-image-small="{{ asset('images/'.$workflowStep['image'].'-small.webp') }}{{ isset($workflowStep['version']) ? '?v='.$workflowStep['version'] : '' }}"
                            data-image-small-width="{{ $workflowStep['smallWidth'] }}"
                            data-image-width="{{ $workflowStep['width'] }}"
                            data-image-height="{{ $workflowStep['height'] }}"
                            data-image-alt="{{ $workflowStep['alt'] }}"
                            aria-controls="workflow-example"
                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                        >
                            <span class="atelier-step__number">{{ $workflowStep['number'] }}</span>
                            <span>{{ $workflowStep['label'] }}</span>
                            @include('partials.icon', ['name' => 'arrow-right'])
                        </button>
                    @endforeach
                </div>
                <p class="atelier__description" id="workflow-description" data-workflow-description aria-live="off">{{ $workflowSteps[0]['description'] }}</p>
                <p class="visually-hidden" data-workflow-live role="status" aria-live="polite">Visar Mottagen i exempelkortet.</p>
                <a class="text-link atelier__demo-link" href="{{ route('demo') }}">Prova exemplet med testuppgifter</a>
            </div>

            <div class="atelier__visual" id="workflow-example">
                <figure class="atelier-photo">
                    <span class="atelier-photo__light" aria-hidden="true"></span>
                    <img
                        class="atelier-photo__image"
                        src="{{ asset('images/workshop-hero.webp') }}"
                        srcset="{{ asset('images/workshop-hero-small.webp') }} 640w, {{ asset('images/workshop-hero.webp') }} 1122w"
                        sizes="(max-width: 720px) calc(100vw - 40px), (max-width: 1200px) 53vw, 680px"
                        alt="En äldre träbåge står på arbetsbänken redo att märkas"
                        width="1200"
                        height="800"
                        loading="eager"
                        decoding="async"
                        data-workflow-image
                    >
                    <span class="atelier-photo__tag" aria-hidden="true">2-KÖK-F1-B2</span>
                </figure>
                <article class="atelier-card" aria-label="Exempelarbetskort, 2-KÖK-F1-B2">
                    <div class="atelier-card__header">
                        <div>
                            <p class="atelier-card__eyebrow">EXEMPELARBETSKORT</p>
                            <h3>2-KÖK-F1-B2</h3>
                            <p>Våning 2 <span aria-hidden="true">·</span> Kök <span aria-hidden="true">·</span> innerbåge</p>
                        </div>
                        <span class="atelier-card__status" data-workflow-status>Mottagen</span>
                    </div>
                    <ol class="atelier-card__progress" aria-label="Arbetssteg i exemplet">
                        @foreach ($workflowSteps as $workflowStep)
                            <li @class(['is-current' => $loop->first]) data-workflow-progress="{{ $workflowStep['key'] }}" @if ($loop->first) aria-current="step" @endif><span aria-hidden="true">{{ $workflowStep['number'] }}</span>{{ $workflowStep['label'] }}</li>
                        @endforeach
                    </ol>
                </article>
            </div>
        </div>
    </section>

    @php
        $labelShotVersion = substr(hash_file('sha256', public_path('images/product/qr-etikett.webp')), 0, 12);
        $reportShotVersion = substr(hash_file('sha256', public_path('images/product/projektrapport.webp')), 0, 12);
        $rotShotVersion = substr(hash_file('sha256', public_path('images/product/rot-underlag.webp')), 0, 12);
        $statusShotVersion = substr(hash_file('sha256', public_path('images/product/statussida.webp')), 0, 12);
        $pricingShotVersion = substr(hash_file('sha256', public_path('images/product/arbetskort-pris.webp')), 0, 12);
        $glazingVersion = substr(hash_file('sha256', public_path('images/workshop-glazing.webp')), 0, 12);
    @endphp
    <section class="guide-ribbon section-space page-shell" aria-labelledby="guide-ribbon-title">
        <div class="guide-ribbon__heading" data-reveal>
            <div>
                <p class="eyebrow">PRAKTISKT I VERKSTADEN</p>
                <h2 id="guide-ribbon-title" class="section-heading">Bra rutiner, steg för steg.</h2>
            </div>
            <p class="guide-ribbon__intro">Korta guider om märkning och dokumentation. <a href="{{ route('guides.index') }}">Alla guider</a></p>
        </div>
        <div class="guide-ribbon__items">
            <a class="guide-teaser" href="{{ route('guides.marking') }}" data-reveal>
                <span class="guide-teaser__visual"><img src="{{ asset('images/workshop-hero-small.webp') }}" srcset="{{ asset('images/workshop-hero-small.webp') }} 640w, {{ asset('images/workshop-hero.webp') }} 1122w" sizes="(max-width: 520px) 38vw, (max-width: 720px) 30vw, 25vw" alt="Träbåge med en numrerad mässingsbricka" width="1122" height="1402" loading="lazy" decoding="async"></span>
                <span class="guide-teaser__copy"><span>Märkning</span>@include('partials.icon', ['name' => 'arrow-right'])</span>
            </a>
            <a class="guide-teaser" href="{{ route('guides.work-card') }}" data-reveal>
                <span class="guide-teaser__visual"><img src="{{ asset('images/workshop-glazing-small.webp') }}?v={{ $glazingVersion }}" srcset="{{ asset('images/workshop-glazing-small.webp') }}?v={{ $glazingVersion }} 600w, {{ asset('images/workshop-glazing.webp') }}?v={{ $glazingVersion }} 1200w" sizes="(max-width: 520px) 38vw, (max-width: 720px) 30vw, 25vw" alt="Kitt stryks mot glaset i en äldre träbåge" width="1200" height="800" loading="lazy" decoding="async"></span>
                <span class="guide-teaser__copy"><span>Arbetskort</span>@include('partials.icon', ['name' => 'arrow-right'])</span>
            </a>
            <a class="guide-teaser" href="{{ route('guides.documentation') }}" data-reveal>
                <span class="guide-teaser__visual"><img src="{{ asset('images/workshop-restored-small.webp') }}" srcset="{{ asset('images/workshop-restored-small.webp') }} 600w, {{ asset('images/workshop-restored.webp') }} 1200w" sizes="(max-width: 520px) 38vw, (max-width: 720px) 30vw, 25vw" alt="Färdigrenoverad träbåge efter arbetet" width="1200" height="800" loading="lazy" decoding="async"></span>
                <span class="guide-teaser__copy"><span>Dokumentation</span>@include('partials.icon', ['name' => 'arrow-right'])</span>
            </a>
            <a class="guide-teaser" href="{{ route('guides.document-renovation') }}" data-reveal>
                <span class="guide-teaser__visual"><img src="{{ asset('images/workshop-painting-small.webp') }}" srcset="{{ asset('images/workshop-painting-small.webp') }} 600w, {{ asset('images/workshop-painting.webp') }} 1200w" sizes="(max-width: 520px) 38vw, (max-width: 720px) 30vw, 25vw" alt="Färg stryks på en äldre träbåge" width="1200" height="800" loading="lazy" decoding="async"></span>
                <span class="guide-teaser__copy"><span>Renovering</span>@include('partials.icon', ['name' => 'arrow-right'])</span>
            </a>
        </div>
    </section>

    <section class="product-proof page-shell" aria-labelledby="product-proof-title">
        <div data-reveal>
            <p class="eyebrow">FRÅN VERKSTADSKONTOT</p>
            <h2 id="product-proof-title" class="section-heading">Så ser kontot ut.</h2>
            <p class="product-proof__intro">Skärmbilder från ett exempel med testuppgifter. Ingen kund och inga omdömen.</p>
        </div>
        @php
            $video = config('bagspar.video');
            $videoId = $video['id'] ?? null;
            $videoLibrary = $video['library_id'] ?? null;
        @endphp
        @if ($videoId && $videoLibrary)
            <div class="product-video" id="se-video" aria-labelledby="video-title" data-reveal>
                <div class="product-video__heading">
                    <div>
                        <p class="eyebrow">BÅGSPÅR I PRAKTIKEN</p>
                        <h3 id="video-title">Från QR-etikett till statuslänk.</h3>
                    </div>
                    <p>Se hur varje båge får en etikett, ett arbetskort med steg och bilder, och hur kunden följer jobbet via en länk.</p>
                </div>
                <p class="product-video__meta" id="video-language">44 sekunder · På svenska · Textad</p>
                <iframe
                    class="product-video__player"
                    src="https://player.mediadelivery.net/embed/{{ $videoLibrary }}/{{ $videoId }}?autoplay=false&amp;loop=false&amp;muted=true&amp;preload=false&amp;responsive=true"
                    title="Film: Bågspår i praktiken"
                    loading="lazy"
                    width="1920"
                    height="1080"
                    allow="autoplay; encrypted-media; picture-in-picture"
                    allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                    aria-describedby="video-language"></iframe>
                <a class="text-link product-video__direct" href="https://player.mediadelivery.net/play/{{ $videoLibrary }}/{{ $videoId }}" target="_blank" rel="noopener noreferrer">Öppna filmen i en ny flik <span aria-hidden="true">↗</span></a>
            </div>
            <script type="application/ld+json">{!! json_encode([
                '@@context' => 'https://schema.org',
                '@type' => 'VideoObject',
                'name' => 'Bågspår i praktiken',
                'description' => 'Se hur varje båge får en QR-etikett, ett arbetskort med steg och bilder, och hur kunden följer jobbet via en statuslänk.',
                'thumbnailUrl' => url('/images/video-poster.jpg'),
                'uploadDate' => $video['upload_date'],
                'duration' => $video['duration'],
                'embedUrl' => 'https://iframe.mediadelivery.net/embed/'.$videoLibrary.'/'.$videoId,
                'inLanguage' => 'sv',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) !!}</script>
        @endif
        <div class="product-proof__items">
            <figure data-reveal>
                <button class="product-proof__open" type="button" data-lightbox-open aria-haspopup="dialog">
                    <span class="visually-hidden">Visa i full storlek.</span>
                    <img src="{{ asset('images/product/arbetskort.webp') }}" alt="Arbetskort för båge 2-KÖK-F1-B2 i exempelverkstaden, med steget Glasning och stegen Mottagen till Klar" width="2560" height="1800" loading="lazy" decoding="async">
                </button>
                <figcaption><strong>Arbetskort</strong> Plats, anteckning och arbetssteg för en båge.</figcaption>
            </figure>
            <figure data-reveal>
                <button class="product-proof__open" type="button" data-lightbox-open aria-haspopup="dialog">
                    <span class="visually-hidden">Visa i full storlek.</span>
                    <img src="{{ asset('images/product/qr-etikett.webp') }}?v={{ $labelShotVersion }}" alt="QR-etikett för båge 2-KÖK-F1-B2, rummet Kök, i Exempelprojekt" width="468" height="218" loading="lazy" decoding="async">
                </button>
                <figcaption><strong>QR-etikett</strong> Bågens nummer på etiketten. Skanningen är till för verkstadens personal och kräver inloggning. Kunden får en separat statuslänk.</figcaption>
            </figure>
            <figure data-reveal>
                <button class="product-proof__open" type="button" data-lightbox-open aria-haspopup="dialog">
                    <span class="visually-hidden">Visa i full storlek.</span>
                    <img src="{{ asset('images/product/projektrapport.webp') }}?v={{ $reportShotVersion }}" alt="Projektrapport för Exempelprojekt från Exempelverkstaden, med logotyp och bildtexterna Före och Efter för båge V2-H" width="2200" height="3066" loading="lazy" decoding="async">
                </button>
                <figcaption><strong>Projektrapport</strong> Samma uppgifter, nedladdade som PDF eller utskrivna.</figcaption>
            </figure>
            <figure data-reveal>
                <button class="product-proof__open" type="button" data-lightbox-open aria-haspopup="dialog">
                    <span class="visually-hidden">Visa i full storlek.</span>
                    <img src="{{ asset('images/product/rot-underlag.webp') }}?v={{ $rotShotVersion }}" alt="ROT-underlag för Exempelprojekt i exempelverkstaden, med summerad arbetskostnad och material samt knapparna Ladda ner CSV och Ladda ner PDF" width="2560" height="1800" loading="lazy" decoding="async">
                </button>
                <figcaption><strong>ROT-underlag</strong> Tid och material summerade per projekt, som PDF eller CSV.</figcaption>
            </figure>
            <figure data-reveal>
                <button class="product-proof__open" type="button" data-lightbox-open aria-haspopup="dialog">
                    <span class="visually-hidden">Visa i full storlek.</span>
                    <img src="{{ asset('images/product/statussida.webp') }}?v={{ $statusShotVersion }}" alt="Statussida för Exempelprojekt som kunden ser utan inloggning, med fyra bågar och deras steg samt ett godkännande av leveransen. Exempeldata." width="2560" height="2640" loading="lazy" decoding="async">
                </button>
                <figcaption><strong>Statussida för kunden</strong> Etapp per båge utan inloggning, med godkännande av leveransen.</figcaption>
            </figure>
        </div>
        <dialog class="lightbox" data-lightbox aria-labelledby="lightbox-title">
            <div class="lightbox__bar">
                <a class="lightbox__full" data-lightbox-full target="_blank" rel="noopener">Visa i full storlek<span class="visually-hidden"> (öppnas i ny flik)</span></a>
                <button class="lightbox__close" type="button" data-lightbox-close>Stäng</button>
            </div>
            <div class="lightbox__panel">
                <div class="lightbox__frame" data-lightbox-frame>
                    <img data-lightbox-image alt="">
                </div>
                <p id="lightbox-title" data-lightbox-caption></p>
            </div>
        </dialog>
    </section>

    <div id="pilot">
        <section id="pris" class="pilot-pricing page-shell" aria-labelledby="pilot-title">
            <div class="pilot-pricing__copy" data-reveal>
                <p class="eyebrow">PRIS</p>
                <h2 id="pilot-title" class="section-heading">{{ \App\Support\Pricing::trialHeadline() }}. Sedan ett pris per verkstad.</h2>
                <p class="body-copy">{{ \App\Support\Pricing::offerSentence() }} {{ \App\Support\Pricing::invoiceSentence() }}</p>
                <div class="pilot-pricing__price">
                    <p class="pilot-pricing__label">Per verkstad, exkl. moms</p>
                    <p class="pilot-pricing__amount"><span>{{ \App\Support\Pricing::kr(\App\Support\Pricing::monthlyKr()) }}</span><span>kr/mån</span></p>
                    <p class="pilot-pricing__vat">{{ \App\Support\Pricing::seatsSentence() }}</p>
                    <p class="pilot-pricing__vat">{{ \App\Support\Pricing::yearlySentence() }}</p>
                    <p class="pilot-pricing__vat">Ingen bindningstid. Faktura, inget kort.</p>
                </div>
                <ul class="pilot-features" aria-label="Det här ingår">
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'project'])</span>
                        <span><strong>Obegränsat med projekt och bågar</strong><small>Arbetskort med plats, anteckningar, tid och material för varje båge. Privata foton. Upp till 20 per båge · högst 5 MB per foto.</small></span>
                    </li>
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'tag'])</span>
                        <span><strong>Utskrivbar QR-etikett</strong><small>Öppnar det privata arbetskortet efter inloggning. Kunden får en separat statuslänk.</small></span>
                    </li>
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'image'])</span>
                        <span><strong>Statuslänk till kunden</strong><small>Kunden ser etapp och bilder utan inloggning och kan godkänna leveransen med namn och tid.</small></span>
                    </li>
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'check'])</span>
                        <span><strong>Offert med godkännande via länk</strong><small>Skriv offerten per båge, skicka länken, och kunden godkänner med sitt namn. Samma projekt följer sedan hela vägen till leverans.</small></span>
                    </li>
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'image'])</span>
                        <span><strong>Mottagningsprotokoll</strong><small>Skick och bilder per båge när bågarna hämtas. Kunden godkänner via länken eller på plats, så ingen tvistar om sprickor i efterhand.</small></span>
                    </li>
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'check'])</span>
                        <span><strong>ROT-underlag</strong><small>Tid och material från arbetskorten summerade per projekt, som PDF eller CSV, redo för fakturan.</small></span>
                    </li>
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'print'])</span>
                        <span><strong>Projektrapport</strong><small>Ladda ner som PDF eller skriv ut.</small></span>
                    </li>
                    <li class="pilot-feature">
                        <span class="pilot-feature__icon" aria-hidden="true">@include('partials.icon', ['name' => 'account'])</span>
                        <span><strong>Medarbetare</strong><small>Ägaren bjuder in medarbetare till samma verkstad. {{ \App\Support\Pricing::seatsSentence() }}</small></span>
                    </li>
                </ul>
                <a class="button" href="{{ route('register') }}">{{ \App\Support\Pricing::trialHeadline() }}</a>
            </div>
            <figure class="pilot-pricing__shot" data-reveal>
                <img src="{{ asset('images/product/arbetskort-pris.webp') }}?v={{ $pricingShotVersion }}" alt="Arbetskort för båge 2-KÖK-F1-B2 i exempelverkstaden, med steget Glasning, stegen Mottagen till Klar och foton före och efter. Exempeldata." width="1600" height="1702" loading="lazy" decoding="async">
            </figure>
        </section>
    </div>

    <section class="faq section-space page-shell" aria-labelledby="faq-title">
        <div class="faq__intro" data-reveal>
            <p class="eyebrow">BRA ATT VETA</p>
            <h2 id="faq-title" class="section-heading">Några saker att veta</h2>
        </div>
        <div class="faq-list" data-reveal>
            @foreach ($homeFaqs as $faq)
                <details>
                    <summary>{{ $faq['question'] }}</summary>
                    <p>{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <section id="kontakt" class="contact-note" aria-labelledby="contact-title">
        <div id="intresse"></div>
        <div class="contact-note__inner page-shell">
            <div class="contact-note__intro" data-reveal>
                <p class="eyebrow">KONTAKT</p>
                <h2 id="contact-title" class="section-heading">Skriv till oss</h2>
                <p>Namn, e-post och ett meddelande räcker, också om du vill ha en genomgång innan verkstaden börjar. Företag och telefon är valfria. Det här formuläret skapar inget konto.</p>
            </div>
            <div class="contact-note__form-wrap" data-reveal>
                @if (session('pilot_lead_duplicate'))
                    <div id="form-result" class="form-result form-result--info" role="status" aria-live="polite" tabindex="-1">
                        <h3>Adressen finns redan i listan.</h3>
                        <p>Vi har redan en förfrågan från den adressen. Frågor? <a href="mailto:{{ config('bagspar.contact_email') }}">Kontakta oss</a>.</p>
                        <a class="text-link" href="{{ route('demo') }}">Prova exempel</a>
                    </div>
                @elseif (session('pilot_lead_received'))
                    <div id="form-result" class="form-result" role="status" aria-live="polite" tabindex="-1">
                        <h3>Tack, vi har fått din förfrågan.</h3>
                        <p>Verkstadens namn och din e-post är sparade. Frågor? <a href="mailto:{{ config('bagspar.contact_email') }}">Kontakta oss</a>.</p>
                        <a class="text-link" href="{{ route('demo') }}">Prova exempel</a>
                    </div>
                @endif
                @include('partials.contact-form', ['idPrefix' => 'home-contact', 'source' => 'home'])
            </div>
        </div>
    </section>

</main>
@endsection

@push('scripts')
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) !!}</script>
@endpush
