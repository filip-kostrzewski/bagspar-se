@extends('layouts.workshop')

@section('title', 'Vänta lite — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">EN SAK I TAGET</p>
        <h1 id="page-title" class="workshop-heading">Vänta lite</h1>
        <p class="auth-page__intro">Det har gjorts många försök på kort tid. Vänta en stund innan du försöker igen.</p>
        <div class="auth-page__links">
            <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ auth()->check() ? route('app.dashboard') : route('home') }}">Till {{ auth()->check() ? 'projektöversikten' : 'Bågspår' }}</a>
        </div>
    </section>
@endsection
