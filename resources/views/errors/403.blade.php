@extends('layouts.workshop')

@section('title', 'Du saknar behörighet — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">403 · INTE DIN NYCKEL</p>
        <h1 id="page-title" class="workshop-heading">Du saknar behörighet</h1>
        <p class="auth-page__intro">Den här åtgärden är förbehållen verkstadens ägare eller ett annat konto. Be ägaren göra det, eller gå tillbaka till verkstadens projekt.</p>
        <div class="auth-page__links">
            <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ auth()->check() ? route('app.dashboard') : route('home') }}">Till {{ auth()->check() ? 'projektöversikten' : 'Bågspår' }}</a>
        </div>
    </section>
@endsection
