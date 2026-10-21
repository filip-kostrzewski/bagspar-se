@extends('layouts.workshop')

@section('title', 'Skapa verkstadskonto — Bågspår')

@section('content')
    <section class="auth-page auth-page--wide" aria-labelledby="page-title">
        <p class="workshop-eyebrow">EN SAMLANDE PLATS FÖR VERKSTADEN</p>
        <h1 id="page-title" class="workshop-heading">Skapa ett konto</h1>
        <p class="auth-page__intro">Börja med verkstaden. Lägg sedan till projekt och bågar när arbetet tar form.</p>

        <aside class="registration-terms" aria-label="Provperiod och pris">
            <strong>{{ \App\Support\Pricing::trialHeadline() }} – inget kort behövs</strong>
            <p>{{ \App\Support\Pricing::offerSentence() }} Vi skickar faktura först när ägaren beställer abonnemanget i appen.</p>
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
                <div class="password-field">
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" aria-describedby="@if ($errors->has('password')) password-hint password-error @else password-hint @endif" required @if ($errors->has('password')) aria-invalid="true" @endif>
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
                <span>Jag godkänner <a class="workshop-text-link" href="{{ route('terms') }}">villkoren</a> och <a class="workshop-text-link" href="{{ route('dpa') }}">personuppgiftsbiträdesavtalet</a>.</span>
            </label>
            @error('terms')
                <p class="form-field__error" id="terms-error">{{ $message }}</p>
            @enderror

            @if (\App\Support\ContactSpam::isTurnstileEnabled())
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                @pushOnce('scripts')
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                @endPushOnce
                @error('turnstile')
                    <p class="form-field__error" id="turnstile-error">{{ $message }}</p>
                @enderror
            @endif

            <p class="auth-privacy-note">Genom att skapa konto kan vi behandla uppgifterna enligt vår <a class="workshop-text-link" href="{{ route('privacy') }}">integritetspolicy</a>.</p>
            <button class="workshop-button workshop-button--primary workshop-button--wide" type="submit">Skapa verkstadskonto</button>
        </form>

        <p class="auth-page__bottom">Har ni redan ett konto? <a class="workshop-text-link" href="{{ route('login') }}">Logga in</a></p>
    </section>
@endsection
