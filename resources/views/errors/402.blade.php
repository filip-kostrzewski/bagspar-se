@extends('layouts.workshop')

@section('title', 'Abonnemang krävs — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">402 · PROVPERIODEN ÄR SLUT</p>
        <h1 id="page-title" class="workshop-heading">Abonnemang krävs</h1>
        <p class="auth-page__intro">Verkstadens provperiod eller abonnemang har gått ut, så den här delen är låst. Dina projekt och bilder finns kvar.</p>
        <div class="auth-page__links">
            @if (auth()->check() && auth()->user()->isOwner())
                <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ route('app.billing.edit') }}">Till abonnemanget</a>
            @elseif (auth()->check())
                <p>Be verkstadens ägare förnya abonnemanget.</p>
                <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ route('app.dashboard') }}">Till projektöversikten</a>
            @else
                <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ route('login') }}">Logga in</a>
            @endif
        </div>
    </section>
@endsection
