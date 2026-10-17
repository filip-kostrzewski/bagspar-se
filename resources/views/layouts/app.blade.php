@php
    $pageLanguage = 'sv';
@endphp
<!doctype html>
<html lang="{{ $pageLanguage }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="@yield('robots', app()->isProduction() ? 'index, follow' : 'noindex, nofollow')">
    <meta name="theme-color" content="#F7F6F1">
    @php
        $pageTitle = trim($__env->yieldContent('title', 'Bågspår — arbetskort för fönsterrenovering'));
        $pageDescription = trim($__env->yieldContent('meta_description', 'Arbetskort för fönsterverkstäder. Märkning, bilder, anteckningar och arbetssteg på varje båge.'));
        $configuredAppUrl = rtrim((string) config('app.url'), '/');
        $canonicalHost = parse_url($configuredAppUrl, PHP_URL_HOST);
        $canonicalUrl = $configuredAppUrl.'/'.ltrim(request()->getPathInfo(), '/');
        $shareImage = $configuredAppUrl.'/images/workshop-hero.webp';
        $contactEmail = config('bagspar.contact_email');
        $organizationId = $configuredAppUrl.'/#organization';
        $organization = [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => 'Prodi Digital AB',
            'url' => $configuredAppUrl.'/',
            'taxID' => '559214-9370',
            'logo' => $configuredAppUrl.'/images/bagspar-mark.svg',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Tegelbrukets väg 41',
                'postalCode' => '195 59',
                'addressLocality' => 'Märsta',
                'addressCountry' => 'SE',
            ],
        ];

        if (is_string($contactEmail) && $contactEmail !== '') {
            $organization['email'] = $contactEmail;
        }

        $siteSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                $organization,
                [
                    '@type' => 'WebSite',
                    'name' => 'Bågspår',
                    'url' => $configuredAppUrl.'/',
                    'inLanguage' => 'sv',
                    'description' => $pageDescription,
                    'publisher' => ['@id' => $organizationId],
                ],
                [
                    '@type' => 'Service',
                    'name' => 'Bågspår',
                    'serviceType' => 'Arbetskort för fönsterrenovering',
                    'description' => 'Arbetskort för fönsterverkstäder, med märkning, bilder, anteckningar och arbetssteg på varje båge.',
                    'provider' => ['@id' => $organizationId],
                    'areaServed' => 'SE',
                    'url' => $configuredAppUrl.'/',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => (string) \App\Support\Pricing::monthlyKr(),
                        'priceCurrency' => 'SEK',
                        'description' => \App\Support\Pricing::trialHeadline().'. '.\App\Support\Pricing::offerSentence(),
                        'url' => $configuredAppUrl.'/#pris',
                    ],
                ],
            ],
        ];
    @endphp
    <meta name="description" content="{{ $pageDescription }}">
    <title>{{ $pageTitle }}</title>
    @if (app()->isProduction() && is_string($canonicalHost) && ! str_ends_with($canonicalHost, '.test'))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="sv_SE">
    <meta property="og:site_name" content="Bågspår">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:width" content="1122">
    <meta property="og:image:height" content="1402">
    <meta property="og:image:alt" content="Äldre träbåge med små glasrutor på en verkstadsbänk">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    <script type="application/ld+json">{!! json_encode($siteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) !!}</script>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ config('bagspar.asset_version') }}">
    @php
        $googleAnalyticsId = \App\Support\GoogleAnalyticsMeasurementId::normalize(
            config('services.google_analytics.measurement_id'),
        );
        $isPublicPage = ! request()->routeIs('admin.*');
        $showGoogleAnalytics = $googleAnalyticsId !== null && $isPublicPage;
    @endphp
    @if ($isPublicPage)
        @include('partials.google-consent')
    @endif
    <script src="{{ asset('js/site.js') }}?v={{ config('bagspar.asset_version') }}" data-sw-url="{{ asset('sw.js') }}?v={{ config('bagspar.asset_version') }}" defer></script>
</head>
<body>
    <a class="skip-link" href="#main">Hoppa till innehållet</a>

    <header class="site-header">
        <div class="site-header__inner page-shell">
            <a class="brand" href="{{ route('home') }}" aria-label="Bågspår, startsida">
                <img class="brand__mark" src="{{ asset('images/bagspar-mark.svg') }}" alt="" width="36" height="36">
                <span class="brand__name">Bågspår</span>
            </a>

            <nav class="site-nav" aria-label="Huvudmeny">
                @if (request()->routeIs('home'))
                    <a class="site-nav__link site-nav__example" href="{{ route('demo') }}">Prova exempel</a>
                @endif
                <a class="site-nav__link site-nav__contact" href="{{ route('contact') }}">Kontakt</a>
                <a class="site-nav__link site-nav__login" href="{{ route('login') }}">Logga in</a>
                <a class="button button--small site-nav__cta" href="{{ route('register') }}">Prova gratis</a>
            </nav>
        </div>
    </header>

    @yield('content')

    <footer class="site-footer">
        <div class="page-shell site-footer__inner">
            <div class="site-footer__brand-block">
                <a class="brand brand--footer" href="{{ route('home') }}" aria-label="Bågspår, startsida">
                    <img class="brand__mark" src="{{ asset('images/bagspar-mark.svg') }}" alt="" width="36" height="36">
                    <span class="brand__name">Bågspår</span>
                </a>
                <p class="site-footer__tagline">Ett enklare arbetsflöde för fönsterrenovering.</p>
                <p class="site-footer__company">Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta</p>
                @if (filled(config('bagspar.contact_email')))
                    <p class="site-footer__contact"><a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a></p>
                @endif
            </div>
            <nav class="site-footer__links" aria-label="Sidfot">
                <a href="{{ route('guides.index') }}">Guider</a>
                <a href="{{ route('guides.documentation') }}">Dokumentation</a>
                <a href="{{ route('guides.work-card') }}">Arbetskort</a>
                <a href="{{ route('guides.marking') }}">Märkning</a>
                <a href="{{ route('privacy') }}">Integritetspolicy</a>
                <a href="{{ route('terms') }}">Villkor</a>
                <a href="{{ route('contact') }}">Kontakt</a>
                @if ($showGoogleAnalytics)
                    <button type="button" data-cookie-settings>Cookie-inställningar</button>
                @endif
            </nav>
            <p class="site-footer__copyright">© {{ now()->year }} Bågspår</p>
        </div>
    </footer>
    @if ($showGoogleAnalytics)
        @include('partials.cookie-banner')
    @endif
    @stack('scripts')
</body>
</html>
