@extends('layouts.workshop')

@section('title', 'Välj nytt lösenord — Bågspår')

@section('content')
    <section class="auth-page" aria-labelledby="page-title">
        <p class="workshop-eyebrow">EN NYCKEL TILL VERKSTADEN</p>
        <h1 id="page-title" class="workshop-heading">Välj ett nytt lösenord</h1>
        <p class="auth-page__intro">Ange det nya lösenordet för ditt konto.</p>

        @if ($errors->any())
            <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                <strong>Kontrollera de markerade uppgifterna.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="workshop-form auth-form" method="POST" action="{{ route('auth.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ is_string(old('email')) ? old('email') : $email }}">

            <div class="form-field">
                <label for="password">Nytt lösenord</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required autofocus @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
                @error('password')
                    <p class="form-field__error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Bekräfta nytt lösenord</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>

            <button class="workshop-button workshop-button--primary workshop-button--wide" type="submit">Spara nytt lösenord</button>
        </form>
    </section>
@endsection
