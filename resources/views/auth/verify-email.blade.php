@extends('layouts.workshop')

@section('title', 'Bekräfta e-postadress — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">EN TRYGG START</p>
        <h1 class="workshop-heading" id="page-title">Bekräfta din e-postadress</h1>
        <p class="auth-page__intro">Följ länken i e-postmeddelandet för att bekräfta adressen och fortsätta till verkstaden. Om meddelandet inte kom fram kan du be om ett nytt.</p>

        @if ($errors->any())
            <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                <strong>Det gick inte att skicka ett nytt meddelande.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="workshop-form auth-form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="workshop-button workshop-button--primary workshop-button--wide" type="submit">Skicka bekräftelselänk igen</button>
        </form>

        <p class="auth-page__bottom">Behöver du lämna kontot? <a class="workshop-text-link" href="{{ route('login') }}">Logga in med en annan adress</a>.</p>
    </section>
@endsection
