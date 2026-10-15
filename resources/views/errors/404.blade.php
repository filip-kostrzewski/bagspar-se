@extends('layouts.workshop')

@section('title', 'Sidan hittades inte — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">404 · FEL VÄG I VERKSTADEN</p>
        <h1 id="page-title" class="workshop-heading">Sidan hittades inte</h1>
        <p class="auth-page__intro">Länken kan vara gammal eller så har adressen ändrats. Gå tillbaka till verkstadens projekt eller till startsidan.</p>
        <div class="auth-page__links">
            <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ auth()->check() ? route('app.dashboard') : route('home') }}">Till {{ auth()->check() ? 'projektöversikten' : 'Bågspår' }}</a>
        </div>
    </section>
@endsection
