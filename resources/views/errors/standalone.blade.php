<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title')</title>
    <style>
        :root { --paper: #f7f6f1; --ink: #17313b; --ink-soft: #516269; --brass: #e7b65a; --rule: #d6dad6; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--paper); color: var(--ink); font-family: system-ui, -apple-system, "Segoe UI", sans-serif; line-height: 1.55; }
        main { width: 100%; max-width: 560px; border-top: 4px solid var(--brass); padding-top: 24px; }
        .eyebrow { margin: 0 0 8px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; color: var(--ink-soft); }
        h1 { margin: 0 0 16px; font-family: Georgia, "Times New Roman", serif; font-size: 2rem; line-height: 1.15; }
        p { margin: 0 0 16px; color: var(--ink-soft); }
        a { color: var(--ink); }
        .button { display: inline-block; padding: 12px 20px; background: var(--ink); color: var(--paper); border-radius: 6px; font-weight: 700; text-decoration: none; }
        .button:focus-visible, a:focus-visible { outline: 3px solid var(--brass); outline-offset: 2px; }
    </style>
</head>
<body>
    <main>
        @yield('content')
    </main>
</body>
</html>
