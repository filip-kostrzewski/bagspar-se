@extends('layouts.workshop')

@section('title', ($customer->exists ? $customer->name : 'Ny kund').' — Bågspår')

@section('content')
    <div class="workspace-page form-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.customers.index') }}">Kunder</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $customer->exists ? $customer->name : 'Ny kund' }}</span>
        </nav>

        <section class="form-page__panel" aria-labelledby="page-title">
            <h1 class="workshop-heading" id="page-title">{{ $customer->exists ? 'Kunduppgifter' : 'Ny kund' }}</h1>

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

            <form class="workshop-form" method="POST" action="{{ $customer->exists ? route('app.customers.update', $customer) : route('app.customers.store') }}">
                @csrf
                @if ($customer->exists)
                    @method('PATCH')
                @endif
                <div class="form-field">
                    <label for="name">Namn</label>
                    <input id="name" name="name" type="text" value="{{ is_string(old('name')) ? old('name') : $customer->name }}" maxlength="160" required>
                </div>
                <div class="form-field">
                    <label for="contact_name">Kontaktperson</label>
                    <input id="contact_name" name="contact_name" type="text" value="{{ is_string(old('contact_name')) ? old('contact_name') : $customer->contact_name }}" maxlength="160">
                </div>
                <div class="form-field">
                    <label for="phone">Telefon</label>
                    <input id="phone" name="phone" type="tel" value="{{ is_string(old('phone')) ? old('phone') : $customer->phone }}" maxlength="40" autocomplete="tel">
                    @if ($customer->phoneHref())
                        <p class="form-field__hint"><a href="{{ $customer->phoneHref() }}">Ring {{ $customer->phone }}</a></p>
                    @endif
                </div>
                <div class="form-field">
                    <label for="address">Adress</label>
                    <input id="address" name="address" type="text" value="{{ is_string(old('address')) ? old('address') : $customer->address }}" maxlength="255">
                </div>
                <div class="form-field">
                    <label for="email">E-post</label>
                    <input id="email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : $customer->email }}" maxlength="254">
                </div>
                <button class="workshop-button workshop-button--primary" type="submit">Spara kund</button>
            </form>

            @if ($customer->exists)
                <div class="customer-privacy">
                    <section aria-labelledby="export-title">
                        <h2 class="workshop-section-heading" id="export-title">Exportera kunddata</h2>
                        <p>JSON och CSV innehåller kunden, projekten, bågarna, historiken och bildlänkarna.</p>
                        <div class="customer-privacy__actions">
                            <a class="workshop-button workshop-button--secondary" href="{{ route('app.customers.export.json', $customer) }}">JSON</a>
                            <a class="workshop-button workshop-button--secondary" href="{{ route('app.customers.export.csv', $customer) }}">CSV</a>
                        </div>
                    </section>

                    @if (auth()->user()?->isOwner())
                        <section class="project-danger-zone" aria-labelledby="erase-title">
                            <h2 class="workshop-section-heading" id="erase-title">Radera kund</h2>
                            <p>Skriv kundens namn. Välj om projekten ska vara kvar utan kunduppgifter eller tas bort tillsammans med bildfilerna.</p>
                            <form class="workshop-form" method="POST" action="{{ route('app.customers.destroy', $customer) }}" data-confirm="Radera kunden? Det går inte att ångra.">
                                @csrf
                                @method('DELETE')
                                <fieldset class="customer-removal">
                                    <legend>Vad ska hända med projekten?</legend>
                                    <label class="workshop-checkbox" for="removal-anonymize">
                                        <input id="removal-anonymize" name="removal" type="radio" value="anonymize" required @checked(old('removal') === 'anonymize')>
                                        <span>Behåll projekten utan kunduppgifter</span>
                                    </label>
                                    <label class="workshop-checkbox" for="removal-delete-projects">
                                        <input id="removal-delete-projects" name="removal" type="radio" value="delete-projects" @checked(old('removal') === 'delete-projects')>
                                        <span>Ta bort projekten och bildfilerna</span>
                                    </label>
                                </fieldset>
                                <div class="form-field">
                                    <label for="confirmation">Skriv {{ $customer->name }} för att bekräfta.</label>
                                    <input id="confirmation" name="confirmation" type="text" value="{{ is_string(old('confirmation')) ? old('confirmation') : '' }}" autocomplete="off" required @if ($errors->has('confirmation')) aria-invalid="true" aria-describedby="confirmation-error" @endif>
                                    @error('confirmation')
                                        <p class="form-field__error" id="confirmation-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button class="workshop-button workshop-button--danger" type="submit">Radera kund</button>
                            </form>
                        </section>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection
