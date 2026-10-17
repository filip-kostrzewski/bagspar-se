@extends('layouts.workshop')

@section('title', 'Den här adressen tar inte emot det — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">405 · FEL SORTS ANROP</p>
        <h1 id="page-title" class="workshop-heading">Den här adressen tar inte emot det</h1>
        <p class="auth-page__intro">Sidan nås bara via en knapp eller ett formulär i Bågspår, inte genom att skriva in adressen. Gå tillbaka och försök därifrån.</p>
        <div class="auth-page__links">
            <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ auth()->check() ? route('app.dashboard') : route('home') }}">Till {{ auth()->check() ? 'projektöversikten' : 'Bågspår' }}</a>
        </div>
    </section>
@endsection
