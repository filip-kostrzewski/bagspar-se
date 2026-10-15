<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Arbetskort för fönsterverkstäder. Märkning, bilder, anteckningar och arbetssteg på varje båge, även när täckningen är svag.')">
    <meta name="robots" content="{{ app()->isProduction() ? 'index, follow' : 'noindex, nofollow' }}">
    <meta name="theme-color" content="#F7F6F1">
    <title>@yield('title', 'Bågspår — arbetskort för fönsterrenovering')</title>
    @php
        $configuredAppUrl = rtrim((string) config('app.url'), '/');
        $canonicalHost = parse_url($configuredAppUrl, PHP_URL_HOST);
        $canonicalUrl = $configuredAppUrl.'/'.ltrim(request()->getPathInfo(), '/');
    @endphp
    @if (app()->isProduction() && is_string($canonicalHost) && ! str_ends_with($canonicalHost, '.test'))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <script src="{{ asset('js/site.js') }}" defer></script>
</head>
<body>
    <a class="skip-link" href="#main">Hoppa till innehållet</a>

    <header class="site-header">
        <div class="site-header__inner page-shell">
            <a class="brand" href="{{ route('home') }}" aria-label="Bågspår, startsida">
                <img class="brand__mark" src="{{ asset('images/bagspar-mark.svg') }}" alt="" width="36" height="36">
                <span class="brand__name">Bågspår</span>
            </a>

            @if (request()->routeIs('home'))
                <nav class="site-nav" aria-label="Huvudmeny">
                    <a class="site-nav__link" href="{{ route('demo') }}">Arbetskortet</a>
                    <a class="site-nav__link" href="#pilot">Piloten</a>
                    <a class="button button--small" href="#intresse">Anmäl verkstad</a>
                </nav>
            @else
                <a class="button button--small" href="{{ route('home') }}#intresse">Anmäl verkstad</a>
            @endif
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
            </div>
            <nav class="site-footer__links" aria-label="Sidfot">
                <a href="{{ route('privacy') }}">Integritetspolicy</a>
                @if (filled(config('bagspar.contact_email')))
                    <a href="mailto:{{ config('bagspar.contact_email') }}">Kontakt</a>
                @endif
            </nav>
            <p class="site-footer__copyright">© {{ now()->year }} Bågspår</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
