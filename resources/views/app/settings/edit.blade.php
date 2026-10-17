@extends('layouts.workshop')

@section('title', 'Verkstadens uppgifter — Bågspår')

@section('content')
    <div class="workspace-page settings-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.dashboard') }}">Projekt</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Inställningar</span>
        </nav>

        <section class="settings-intro" aria-labelledby="page-title">
            <p class="workshop-eyebrow">PÅ PROJEKTRAPPORTEN</p>
            <h1 class="workshop-heading" id="page-title">Verkstadens uppgifter</h1>
            <p class="workspace-lede">Namn, logotyp, kontakt och organisationsnummer visas i sidhuvudet när du skriver ut en projektrapport.</p>
        </section>

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

        <form class="workshop-form settings-form" method="POST" action="{{ route('app.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="form-field">
                <label for="name">Verkstadens namn</label>
                <input id="name" name="name" type="text" value="{{ is_string(old('name')) ? old('name') : $workshop->name }}" maxlength="160" required @if ($errors->has('name')) aria-invalid="true" aria-describedby="settings-name-error" @endif>
                @error('name')
                    <p class="form-field__error" id="settings-name-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="organization_number">Organisationsnummer</label>
                <input id="organization_number" name="organization_number" type="text" value="{{ is_string(old('organization_number')) ? old('organization_number') : $workshop->organization_number }}" inputmode="numeric" maxlength="11" placeholder="559214-9370" @if ($errors->has('organization_number')) aria-invalid="true" aria-describedby="organization-number-error" @endif>
                @error('organization_number')
                    <p class="form-field__error" id="organization-number-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="contact_email">E-post</label>
                <input id="contact_email" name="contact_email" type="email" value="{{ is_string(old('contact_email')) ? old('contact_email') : $workshop->contact_email }}" maxlength="254" inputmode="email" autocomplete="email" @if ($errors->has('contact_email')) aria-invalid="true" aria-describedby="contact-email-error" @endif>
                @error('contact_email')
                    <p class="form-field__error" id="contact-email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="address">Adress</label>
                <input id="address" name="address" type="text" value="{{ is_string(old('address')) ? old('address') : $workshop->address }}" maxlength="255" autocomplete="street-address" @if ($errors->has('address')) aria-invalid="true" aria-describedby="address-error" @endif>
                @error('address')
                    <p class="form-field__error" id="address-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <span id="logo-label">Logotyp</span>
                @if ($workshop->hasLogo())
                    <img class="settings-logo-preview" src="{{ route('app.settings.logo') }}" alt="Nuvarande logotyp för {{ $workshop->name }}" width="72" height="72">
                @endif
                <div class="file-picker">
                    <label class="visually-hidden" for="logo">Logotypfil</label>
                    <input id="logo" class="visually-hidden" name="logo" type="file" accept="image/jpeg,image/png,image/webp" @if ($errors->has('logo')) aria-invalid="true" aria-describedby="logo-error" @endif>
                    <button class="workshop-button workshop-button--secondary" type="button" data-file-picker="logo" aria-describedby="logo-label">Välj bild</button>
                    <span class="file-picker__name" data-file-name>Ingen bild vald</span>
                </div>
                <p class="form-field__hint" id="logo-hint">JPG, PNG eller WebP. Högst 2 MB.</p>
                @error('logo')
                    <p class="form-field__error" id="logo-error">{{ $message }}</p>
                @enderror
            </div>

            @if ($workshop->hasLogo())
                <label class="workshop-checkbox" for="remove_logo">
                    <input id="remove_logo" name="remove_logo" type="checkbox" value="1" @checked(old('remove_logo'))>
                    <span>Ta bort nuvarande logotyp</span>
                </label>
            @endif

            <button class="workshop-button workshop-button--primary" type="submit">Spara uppgifter</button>
        </form>
    </div>
@endsection
