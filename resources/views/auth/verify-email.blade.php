@extends('layouts.workshop')

@section('title', 'Bekräfta e-postadress — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">EN TRYGG START</p>
        <h1 class="workshop-heading" id="page-title">{{ $emailVerified ? 'E-postadressen är bekräftad' : 'Bekräfta din e-postadress' }}</h1>
        @if ($emailVerified)
            <p class="auth-page__intro">Kontot är redan bekräftat och redo att användas.</p>
            <p class="auth-page__bottom">Du är inloggad som <strong>{{ $email }}</strong>.</p>
        @else
            <p class="auth-page__intro">Följ länken i e-postmeddelandet för att bekräfta adressen och fortsätta till verkstaden. Om meddelandet inte kom fram kan du be om ett nytt.</p>
            <p class="auth-page__bottom">Verifieringslänken skickas till <strong>{{ $email }}</strong>.</p>
        @endif

        @if (session('verification_issue'))
            <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                {{ session('verification_issue') }}
            </div>
        @endif

        @if ($emailVerified)
            <p>Adressen för kontot du är inloggad på är redan bekräftad.</p>
            <a class="workshop-button workshop-button--primary workshop-button--wide" href="{{ route('app.dashboard') }}">Till min verkstad</a>
        @else
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
        @endif

        <form class="auth-page__bottom" method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <span>Vill du byta konto?</span>
            <button class="workshop-button workshop-button--quiet" type="submit">Logga ut och byt konto</button>
        </form>
    </section>
@endsection
