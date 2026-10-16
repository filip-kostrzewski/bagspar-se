@extends('layouts.workshop')

@section('title', 'Logga in — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">TILLBAKA I VERKSTADEN</p>
        <h1 id="page-title" class="workshop-heading">Logga in</h1>
        <p class="auth-page__intro">Fortsätt där verkstadens arbete väntar.</p>

        @if (is_string(session('email_verified_notice')))
            <div class="auth-notice" role="status">
                <span class="auth-notice__icon" aria-hidden="true">✓</span>
                <p>{{ session('email_verified_notice') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                <strong>Kontrollera uppgifterna och försök igen.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="workshop-form auth-form" method="POST" action="{{ route('auth.login.store') }}">
            @csrf
            <div class="form-field">
                <label for="email">E-postadress</label>
                <input id="email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : '' }}" autocomplete="username" inputmode="email" maxlength="254" required autofocus @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                @error('email')
                    <p class="form-field__error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="password">Lösenord</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
                @error('password')
                    <p class="form-field__error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="workshop-checkbox" for="remember">
                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                <span>Håll mig inloggad på den här enheten</span>
            </label>

            <button class="workshop-button workshop-button--primary workshop-button--wide" type="submit">Logga in</button>
        </form>

        <div class="auth-page__links">
            <a class="workshop-text-link" href="{{ route('auth.password.request') }}">Glömt lösenordet?</a>
            <p>Har ni inget konto? <a class="workshop-text-link" href="{{ route('register') }}">Skapa verkstadskonto</a></p>
        </div>
    </section>
@endsection
