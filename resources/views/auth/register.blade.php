@extends('layouts.workshop')

@section('title', 'Skapa verkstadskonto — Bågspår')

@section('content')
    <section class="auth-page auth-page--wide" aria-labelledby="page-title">
        <p class="workshop-eyebrow">EN SAMLANDE PLATS FÖR VERKSTADEN</p>
        <h1 id="page-title" class="workshop-heading">Skapa ett konto</h1>
        <p class="auth-page__intro">Börja med verkstaden. Lägg sedan till projekt och bågar när arbetet tar form.</p>

        <aside class="registration-terms" aria-label="Provperiod och pris">
            <strong>3 månaders kostnadsfri provperiod</strong>
            <p>Därefter 790 kr/mån per verkstad, exkl. moms. Betalning kan börja först när den aktiveras manuellt — inget dras automatiskt.</p>
        </aside>

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

        <form class="workshop-form auth-form" method="POST" action="{{ route('auth.register.store') }}">
            @csrf
            <div class="form-field">
                <label for="name">Ditt namn</label>
                <input id="name" name="name" type="text" value="{{ is_string(old('name')) ? old('name') : '' }}" autocomplete="name" maxlength="150" required @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                @error('name')
                    <p class="form-field__error" id="name-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="company">Företag eller verkstad</label>
                <input id="company" name="company" type="text" value="{{ is_string(old('company')) ? old('company') : '' }}" autocomplete="organization" maxlength="160" required @if ($errors->has('company')) aria-invalid="true" aria-describedby="company-error" @endif>
                @error('company')
                    <p class="form-field__error" id="company-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="email">E-postadress</label>
                <input id="email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : '' }}" autocomplete="email" inputmode="email" maxlength="254" required @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                @error('email')
                    <p class="form-field__error" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="password">Lösenord</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" aria-describedby="@if ($errors->has('password')) password-hint password-error @else password-hint @endif" required @if ($errors->has('password')) aria-invalid="true" @endif>
                <p class="form-field__hint" id="password-hint">Minst 12 tecken.</p>
                @error('password')
                    <p class="form-field__error" id="password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Bekräfta lösenord</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>

            <p class="auth-privacy-note">Genom att skapa konto kan vi behandla uppgifterna enligt vår <a class="workshop-text-link" href="{{ route('privacy') }}">integritetspolicy</a>.</p>
            <button class="workshop-button workshop-button--primary workshop-button--wide" type="submit">Skapa verkstadskonto</button>
        </form>

        <p class="auth-page__bottom">Har ni redan ett konto? <a class="workshop-text-link" href="{{ route('login') }}">Logga in</a></p>
    </section>
@endsection
