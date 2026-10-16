@extends('layouts.workshop')

@section('title', 'Verifieringslänken fungerar inte — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">LÄNKEN KAN INTE ANVÄNDAS</p>
        <h1 id="page-title" class="workshop-heading">Länken är ogiltig eller har gått ut</h1>
        <p class="auth-page__intro">Den här verifieringslänken kan inte bekräfta e-postadressen. Be om en ny länk från kontot, eller logga in om adressen redan är bekräftad.</p>
        <div class="auth-page__links">
            @auth
                <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ route('verification.notice') }}">Hantera bekräftelsen</a>
            @else
                <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ route('login') }}">Logga in</a>
            @endauth
            <p><a class="workshop-text-link" href="{{ route('home') }}">Till Bågspår</a></p>
        </div>
    </section>
@endsection
