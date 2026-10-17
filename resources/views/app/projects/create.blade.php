@extends('layouts.workshop')

@section('title', 'Nytt projekt — Bågspår')

@section('content')
    <div class="workspace-page form-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.dashboard') }}">Projekt</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Nytt projekt</span>
        </nav>

        <section class="form-page__panel" aria-labelledby="page-title">
            <p class="workshop-eyebrow">ETT NYTT ARBETE</p>
            <h1 class="workshop-heading" id="page-title">Skapa projekt</h1>
            <p class="workspace-lede">Börja med namnet. Kund, plats och anteckningar kan fyllas i nu eller senare.</p>

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

            <form class="workshop-form project-form" method="POST" action="{{ route('app.projects.store') }}">
                @csrf
                <div class="form-field">
                    <label for="name">Projektnamn <span class="form-required">Obligatoriskt</span></label>
                    <input id="name" name="name" type="text" value="{{ is_string(old('name')) ? old('name') : '' }}" autocomplete="off" maxlength="160" required autofocus @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                    @error('name')
                        <p class="form-field__error" id="name-error">{{ $message }}</p>
                    @enderror
                </div>

                @include('partials.workshop.customer-fields', [
                    'prefix' => '',
                    'customers' => $customers,
                    'selectedCustomerId' => old('customer_id'),
                ])

                <div class="form-field">
                    <label for="location">Adress eller plats <span class="form-optional">Valfritt</span></label>
                    <input id="location" name="location" type="text" value="{{ is_string(old('location')) ? old('location') : '' }}" autocomplete="street-address" maxlength="255" @if ($errors->has('location')) aria-invalid="true" aria-describedby="location-error" @endif>
                    @error('location')
                        <p class="form-field__error" id="location-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="notes">Anteckningar <span class="form-optional">Valfritt</span></label>
                    <textarea id="notes" name="notes" rows="5" maxlength="5000" @if ($errors->has('notes')) aria-invalid="true" aria-describedby="notes-error" @endif>{{ is_string(old('notes')) ? old('notes') : '' }}</textarea>
                    @error('notes')
                        <p class="form-field__error" id="notes-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="workshop-checkbox" for="notify-customer-when-ready">
                        <input id="notify-customer-when-ready" name="notify_customer_when_ready" type="checkbox" value="1" @checked(old('notify_customer_when_ready'))>
                        <span>Mejla kunden när alla bågar är klara</span>
                    </label>
                    <p class="form-field__hint">Mejlet skickas bara om kunden har en e-postadress. Det innehåller statuslänken och inga arbetsanteckningar.</p>
                </div>

                <div class="form-actions">
                    <button class="workshop-button workshop-button--primary" type="submit">Spara projekt</button>
                    <a class="workshop-button workshop-button--quiet" href="{{ route('app.dashboard') }}">Avbryt</a>
                </div>
            </form>
        </section>
    </div>
@endsection
