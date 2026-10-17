@php
    $pageTitle = trim($__env->yieldContent('title', 'Bågspår — verkstaden'));
@endphp
<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#F7F6F1">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('fonts/fraunces-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/manrope-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/workshop.css') }}?v={{ substr(hash_file('sha256', public_path('css/workshop.css')), 0, 12) }}">
    <script src="{{ asset('js/workshop.js') }}?v={{ substr(hash_file('sha256', public_path('js/workshop.js')), 0, 12) }}" defer></script>
</head>
<body class="workshop-app @yield('body_class')">
    <a class="workshop-skip-link" href="#main">Hoppa till innehållet</a>

    <header class="workshop-header" data-workshop-chrome>
        <div class="workshop-header__inner page-shell">
            <a class="workshop-brand" href="{{ auth()->check() ? route('app.dashboard') : route('home') }}" aria-label="Bågspår, {{ auth()->check() ? 'verkstaden' : 'startsidan' }}">
                <img class="workshop-brand__mark" src="{{ asset('images/bagspar-mark.svg') }}" alt="" width="36" height="36">
                <span class="workshop-brand__name">Bågspår</span>
                @auth
                    <span class="workshop-brand__context">Verkstaden</span>
                @endauth
            </a>

            @auth
                <div class="workshop-header__account">
                    <span class="workshop-header__workspace">@isset($workshop){{ $workshop->name }}@else{{ auth()->user()->name }}@endisset</span>
                    <nav class="workshop-nav" aria-label="Verkstadsmeny">
                        <a href="{{ route('app.dashboard') }}" @if (request()->routeIs('app.dashboard', 'app.projects.*', 'app.sashes.*')) aria-current="page" @endif>Projekt</a>
                        <a href="{{ route('app.settings.edit') }}" @if (request()->routeIs('app.settings.*')) aria-current="page" @endif>Inställningar</a>
                    </nav>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button class="workshop-button workshop-button--quiet workshop-header__logout" type="submit">Logga ut</button>
                    </form>
                </div>
            @else
                <a class="workshop-nav__public-link" href="{{ route('home') }}">Till Bågspår</a>
            @endauth
        </div>
    </header>

    @if (session('status'))
        <div class="workshop-flash workshop-flash--success" role="status" tabindex="-1" data-workshop-focus>
            <span class="workshop-flash__icon" aria-hidden="true">✓</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <main id="main" class="workshop-main page-shell">
        @yield('content')
    </main>

    <footer class="workshop-footer" data-workshop-chrome>
        <div class="workshop-footer__inner page-shell">
            <span>Ordning på varje båge.</span>
            <a href="{{ route('privacy') }}">Integritet</a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
