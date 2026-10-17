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

        <section class="staff-panel" aria-labelledby="staff-title">
            <p class="workshop-eyebrow">ÅTKOMST</p>
            <h2 class="workshop-section-heading" id="staff-title">Medarbetare</h2>
            <p class="staff-panel__intro">Ägaren bjuder in medarbetare via e-post. En medarbetare kan arbeta med projekt och bågar, men inte ändra de här uppgifterna eller ta bort projekt.</p>

            <ul class="staff-list">
                @foreach ($members as $member)
                    <li class="staff-list__item">
                        <div>
                            <strong>{{ $member->name }}</strong>
                            <p class="staff-list__meta">{{ $member->email }} · {{ $member->roleLabel() }}</p>
                        </div>
                        @if ($member->isMember())
                            <form method="POST" action="{{ route('app.members.destroy', $member) }}">
                                @csrf
                                @method('DELETE')
                                <button class="workshop-button workshop-button--quiet" type="submit">Ta bort åtkomst</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($invitations->isNotEmpty())
                <h3 class="staff-panel__subheading">Väntande inbjudningar</h3>
                <ul class="staff-list">
                    @foreach ($invitations as $invitation)
                        <li class="staff-list__item">
                            <div>
                                <strong>{{ $invitation->email }}</strong>
                                <p class="staff-list__meta">Gäller till {{ $invitation->expires_at?->timezone('Europe/Stockholm')->format('Y-m-d H:i') }}</p>
                            </div>
                            <form method="POST" action="{{ route('app.invitations.destroy', $invitation) }}">
                                @csrf
                                @method('DELETE')
                                <button class="workshop-button workshop-button--quiet" type="submit">Ta bort inbjudan</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form class="workshop-form settings-form" method="POST" action="{{ route('app.invitations.store') }}">
                @csrf
                <div class="form-field">
                    <label for="invite-email">Bjud in med e-post</label>
                    <input id="invite-email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : '' }}" maxlength="254" required autocomplete="off" @if ($errors->has('email')) aria-invalid="true" aria-describedby="invite-email-error" @endif>
                    @error('email')
                        <p class="form-field__error" id="invite-email-error">{{ $message }}</p>
                    @enderror
                </div>
                <button class="workshop-button workshop-button--secondary" type="submit">Skicka inbjudan</button>
            </form>
        </section>
    </div>
@endsection
