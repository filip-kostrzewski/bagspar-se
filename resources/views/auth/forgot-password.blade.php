@extends('layouts.workshop')

@section('title', 'Återställ lösenord — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">KOM TILLBAKA TILL VERKSTADEN</p>
        <h1 id="page-title" class="workshop-heading">Återställ lösenordet</h1>
        <p class="auth-page__intro">Skriv in e-postadressen för kontot. Om den finns registrerad skickar vi en länk för att välja ett nytt lösenord.</p>

        @if ($errors->any())
            <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                <strong>Kontrollera e-postadressen.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="workshop-form auth-form" method="POST" action="{{ route('auth.password.email') }}">
            @csrf
            <div class="form-field">
                <label for="email">E-postadress</label>
                <input id="email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : '' }}" autocomplete="email" inputmode="email" maxlength="254" required autofocus @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                @error('email')
                    <p class="form-field__error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="workshop-button workshop-button--primary workshop-button--wide" type="submit">Skicka återställningslänk</button>
        </form>

        <p class="auth-page__bottom"><a class="workshop-text-link" href="{{ route('login') }}">Tillbaka till inloggningen</a></p>
    </section>
@endsection
