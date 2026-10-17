@extends('layouts.app')

@section('title', 'Kontakt — Bågspår')
@section('meta_description', 'Skriv till Bågspår. Namn, e-post och meddelande behövs. Företag och telefon är valfria.')

@section('content')
<main id="main" class="privacy-page page-shell">
    <a class="text-link privacy-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">KONTAKT</p>
    <h1 id="page-title" class="display-heading">Skriv till oss</h1>
    <div class="privacy-page__body body-copy">
        <p>Namn, e-post och ett meddelande räcker. Företag och telefon är valfria.</p>
        <div id="kontakt" class="contact-page__form">
            @include('partials.contact-form', ['idPrefix' => 'page-contact', 'source' => 'page'])
        </div>
    </div>
</main>
@endsection
