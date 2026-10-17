@extends('layouts.workshop')

@section('title', 'Mitt konto — Bågspår')

@section('content')
    @php
        $profileErrors = $errors->getBag('profile');
        $passwordErrors = $errors->getBag('password');
    @endphp
    <div class="workspace-page account-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.dashboard') }}">Projekt</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Mitt konto</span>
        </nav>

        <section class="settings-intro" aria-labelledby="page-title">
            <p class="workshop-eyebrow">{{ $workshop->name }}</p>
            <h1 class="workshop-heading" id="page-title">Mitt konto</h1>
            <p class="workspace-lede">Ditt namn, din e-postadress och ditt lösenord. Ändringarna gäller bara dig.</p>
        </section>

        <dl class="account-summary" aria-label="Din roll">
            <div>
                <dt>Roll</dt>
                <dd>{{ $user->roleLabel() }}</dd>
            </div>
            <div>
                <dt>Verkstad</dt>
                <dd>{{ $workshop->name }}</dd>
            </div>
        </dl>
        <p class="account-note">Verkstadens uppgifter ändras av ägaren under Inställningar.</p>

        <section class="account-section" aria-labelledby="profile-title">
            <p class="workshop-eyebrow">UPPGIFTER</p>
            <h2 class="workshop-section-heading" id="profile-title">Namn och e-post</h2>

            @if ($profileErrors->any())
                <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                    <strong>Kontrollera de markerade uppgifterna.</strong>
                    <ul>
                        @foreach ($profileErrors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="workshop-form settings-form" method="POST" action="{{ route('app.account.update') }}">
                @csrf
                @method('PATCH')

                <div class="form-field">
                    <label for="name">Namn</label>
                    <input id="name" name="name" type="text" value="{{ is_string(old('name')) ? old('name') : $user->name }}" maxlength="150" autocomplete="name" required @if ($profileErrors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                    @error('name', 'profile')
                        <p class="form-field__error" id="name-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="email">E-post</label>
                    <input id="email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : $user->email }}" maxlength="254" inputmode="email" autocomplete="email" required aria-describedby="@if ($profileErrors->has('email')) email-hint email-error @else email-hint @endif" @if ($profileErrors->has('email')) aria-invalid="true" @endif>
                    <p class="form-field__hint" id="email-hint">Byter du adress måste du bekräfta den nya via ett mejl.</p>
                    @error('email', 'profile')
                        <p class="form-field__error" id="email-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="profile_current_password">Nuvarande lösenord</label>
                    <div class="password-field">
                        <input id="profile_current_password" name="current_password" type="password" autocomplete="current-password" required @if ($profileErrors->has('current_password')) aria-invalid="true" aria-describedby="profile-current-password-error" @endif>
                        <button class="workshop-button workshop-button--quiet" type="button" data-password-toggle="profile_current_password" aria-pressed="false">Visa</button>
                    </div>
                    @error('current_password', 'profile')
                        <p class="form-field__error" id="profile-current-password-error">{{ $message }}</p>
                    @enderror
                </div>

                <button class="workshop-button workshop-button--primary" type="submit">Spara uppgifter</button>
            </form>
        </section>

        <section class="account-section" aria-labelledby="password-title">
            <p class="workshop-eyebrow">SÄKERHET</p>
            <h2 class="workshop-section-heading" id="password-title">Byt lösenord</h2>
            <p class="staff-panel__intro">När lösenordet är bytt loggas du ut på dina andra enheter.</p>

            @if ($passwordErrors->any())
                <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                    <strong>Kontrollera de markerade uppgifterna.</strong>
                    <ul>
                        @foreach ($passwordErrors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="workshop-form settings-form" method="POST" action="{{ route('app.account.password') }}">
                @csrf
                @method('PATCH')

                <div class="form-field">
                    <label for="password_current_password">Nuvarande lösenord</label>
                    <div class="password-field">
                        <input id="password_current_password" name="current_password" type="password" autocomplete="current-password" required @if ($passwordErrors->has('current_password')) aria-invalid="true" aria-describedby="password-current-password-error" @endif>
                        <button class="workshop-button workshop-button--quiet" type="button" data-password-toggle="password_current_password" aria-pressed="false">Visa</button>
                    </div>
                    @error('current_password', 'password')
                        <p class="form-field__error" id="password-current-password-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password">Nytt lösenord</label>
                    <div class="password-field">
                        <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required aria-describedby="@if ($passwordErrors->has('password')) password-hint password-error @else password-hint @endif" @if ($passwordErrors->has('password')) aria-invalid="true" @endif>
                        <button class="workshop-button workshop-button--quiet" type="button" data-password-toggle="password" aria-pressed="false">Visa</button>
                    </div>
                    <p class="form-field__hint" id="password-hint">Minst 12 tecken.</p>
                    @error('password', 'password')
                        <p class="form-field__error" id="password-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Bekräfta nytt lösenord</label>
                    <div class="password-field">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        <button class="workshop-button workshop-button--quiet" type="button" data-password-toggle="password_confirmation" aria-pressed="false">Visa</button>
                    </div>
                </div>

                <button class="workshop-button workshop-button--primary" type="submit">Byt lösenord</button>
            </form>
        </section>
    </div>
@endsection
