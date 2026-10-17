@php
    $pageTitle = trim($__env->yieldContent('title', 'Status — Bågspår'));
@endphp
<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/workshop.css') }}?v={{ substr(hash_file('sha256', public_path('css/workshop.css')), 0, 12) }}">
</head>
<body class="client-status-page">
    <main id="main" class="client-status-main">
        @yield('content')
    </main>
    <dialog class="status-lightbox" data-enlarge-dialog>
        <form method="dialog">
            <button class="workshop-button workshop-button--quiet" type="submit">Stäng</button>
        </form>
        <img alt="" decoding="async">
    </dialog>
    <script>
        document.querySelectorAll('[data-enlarge]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const dialog = document.querySelector('[data-enlarge-dialog]');
                const image = dialog?.querySelector('img');

                if (!(dialog instanceof HTMLDialogElement) || !(image instanceof HTMLImageElement)) {
                    return;
                }

                event.preventDefault();
                image.src = link.href;
                image.alt = link.querySelector('img')?.alt ?? '';
                dialog.showModal();
            });
        });
    </script>
</body>
</html>
