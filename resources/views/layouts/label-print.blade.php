@php
    $pageTitle = trim($__env->yieldContent('title', 'Etikett — Bågspår'));
@endphp
<!doctype html>
<html lang="sv" class="label-print-standalone @yield('body_class')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/workshop.css') }}?v={{ substr(hash_file('sha256', public_path('css/workshop.css')), 0, 12) }}">
    @yield('print_style')
    <script src="{{ asset('js/workshop.js') }}?v={{ substr(hash_file('sha256', public_path('js/workshop.js')), 0, 12) }}" defer></script>
</head>
<body class="workshop-app label-print-standalone @yield('body_class')">
    <main id="main" class="label-print-main">
        @yield('content')
    </main>
</body>
</html>
