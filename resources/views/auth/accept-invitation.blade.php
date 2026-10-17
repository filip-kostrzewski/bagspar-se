@extends('layouts.workshop')

@section('title', 'Acceptera inbjudan — Bågspår')

@section('content')
    <section class="auth-page auth-page--wide" aria-labelledby="page-title">
        <p class="workshop-eyebrow">MEDARBETARE</p>
        <h1 id="page-title" class="workshop-heading">Välj lösenord</h1>
        <p class="auth-page__intro">{{ $invitation->workshop?->name }} har bjudit in {{ $invitation->email }} som medarbetare. Du kan arbeta med projekt och bågar. Verkstadens uppgifter och borttagning av projekt hör till ägaren.</p>

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

        @auth
            <p>Logga ut innan du accepterar inbjudan.</p>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button class="workshop-button workshop-button--primary" type="submit">Logga ut</button>
            </form>
        @else
            <form class="workshop-form auth-form" method="POST" action="{{ route('invitations.accept.store', ['token' => $token]) }}">
                @csrf
                <div class="form-field">
                    <label for="name">Ditt namn</label>
                    <input id="name" name="name" type="text" value="{{ is_string(old('name')) ? old('name') : '' }}" maxlength="150" required autocomplete="name" @if ($errors->has('name')) aria-invalid="true" aria-describedby="invite-name-error" @endif>
                    @error('name')
                        <p class="form-field__error" id="invite-name-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-field">
                    <label for="password">Lösenord</label>
                    <div class="password-field">
                        <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
                        <button class="workshop-button workshop-button--quiet" type="button" data-password-toggle="password" aria-pressed="false">Visa</button>
                    </div>
                    <p class="form-field__hint" id="password-hint">Minst 12 tecken.</p>
                    @error('password')
                        <p class="form-field__error" id="password-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-field">
                    <label for="password_confirmation">Bekräfta lösenord</label>
                    <div class="password-field">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        <button class="workshop-button workshop-button--quiet" type="button" data-password-toggle="password_confirmation" aria-pressed="false">Visa</button>
                    </div>
                </div>
                <label class="workshop-checkbox" for="terms">
                    <input id="terms" name="terms" type="checkbox" value="1" required @checked(old('terms')) @if ($errors->has('terms')) aria-invalid="true" aria-describedby="terms-error" @endif>
                    <span>Jag godkänner <a class="workshop-text-link" href="{{ route('terms') }}">villkoren</a>.</span>
                </label>
                @error('terms')
                    <p class="form-field__error" id="terms-error">{{ $message }}</p>
                @enderror
                <button class="workshop-button workshop-button--primary workshop-button--wide" type="submit">Gå med i verkstaden</button>
            </form>
        @endauth
    </section>
@endsection
