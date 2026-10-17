@php
    $contactErrors = $errors->getBag('contact');
@endphp

@if (filled(config('bagspar.contact_email')))
    <p class="interest-form__note"><a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a></p>
@endif

@if (session('contact_message_received'))
    <div id="kontakt-resultat" class="form-result" role="status" aria-live="polite" tabindex="-1">
        <p>Tack! Vi har fått ditt meddelande och återkommer så snart vi kan.</p>
    </div>
@else
    <form class="interest-form contact-form" action="{{ route('contact.store') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="source" value="{{ $source }}">

        <div class="form-honeypot" aria-hidden="true">
            <label for="{{ $idPrefix }}-website">Lämna det här fältet tomt</label>
            <input id="{{ $idPrefix }}-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="form-field">
            <label for="{{ $idPrefix }}-name">Namn <span aria-hidden="true">*</span></label>
            <input id="{{ $idPrefix }}-name" name="contact_name" type="text" autocomplete="name" maxlength="150" required value="{{ old('contact_name') }}" @if ($contactErrors->has('contact_name')) aria-invalid="true" aria-describedby="{{ $idPrefix }}-name-error" @endif>
            @error('contact_name', 'contact')<p id="{{ $idPrefix }}-name-error" class="field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="form-field">
            <label for="{{ $idPrefix }}-company">Företag</label>
            <input id="{{ $idPrefix }}-company" name="contact_company" type="text" autocomplete="organization" maxlength="160" value="{{ old('contact_company') }}" @if ($contactErrors->has('contact_company')) aria-invalid="true" aria-describedby="{{ $idPrefix }}-company-error" @endif>
            @error('contact_company', 'contact')<p id="{{ $idPrefix }}-company-error" class="field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="form-field">
            <label for="{{ $idPrefix }}-email">E-post <span aria-hidden="true">*</span></label>
            <input id="{{ $idPrefix }}-email" name="contact_email" type="email" autocomplete="email" inputmode="email" maxlength="254" required value="{{ old('contact_email') }}" @if ($contactErrors->has('contact_email')) aria-invalid="true" aria-describedby="{{ $idPrefix }}-email-error" @endif>
            @error('contact_email', 'contact')<p id="{{ $idPrefix }}-email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="form-field">
            <label for="{{ $idPrefix }}-phone">Telefon</label>
            <input id="{{ $idPrefix }}-phone" name="contact_phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="40" value="{{ old('contact_phone') }}" @if ($contactErrors->has('contact_phone')) aria-invalid="true" aria-describedby="{{ $idPrefix }}-phone-error" @endif>
            @error('contact_phone', 'contact')<p id="{{ $idPrefix }}-phone-error" class="field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="form-field">
            <label for="{{ $idPrefix }}-message">Meddelande <span aria-hidden="true">*</span></label>
            <textarea id="{{ $idPrefix }}-message" name="contact_message" autocomplete="off" maxlength="2000" required rows="4" @if ($contactErrors->has('contact_message')) aria-invalid="true" aria-describedby="{{ $idPrefix }}-message-error" @endif>{{ old('contact_message') }}</textarea>
            @error('contact_message', 'contact')<p id="{{ $idPrefix }}-message-error" class="field-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <button class="button interest-form__submit" type="submit">Skicka meddelande</button>
        <p class="interest-form__note">Vi använder uppgifterna bara för att svara på ditt meddelande, se <a href="/integritet">integritetspolicyn</a>.</p>
    </form>
@endif
