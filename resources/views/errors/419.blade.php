@extends('layouts.workshop')

@section('title', 'Sessionen har gått ut — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">FORMULÄRET BEHÖVER EN NY SESSION</p>
        <h1 id="page-title" class="workshop-heading">Sessionen har gått ut</h1>
        <p class="auth-page__intro">Ladda om sidan och skicka formuläret igen. Om du har loggats ut kan du logga in på nytt.</p>
        <div class="auth-page__links">
            <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ auth()->check() ? route('app.dashboard') : route('home') }}">Till {{ auth()->check() ? 'projektöversikten' : 'Bågspår' }}</a>
            <p><a class="workshop-text-link" href="{{ route('login') }}">Logga in igen</a></p>
        </div>
    </section>
@endsection
