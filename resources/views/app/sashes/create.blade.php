@extends('layouts.workshop')

@section('title', 'Lägg till båge — '.$project->name.' — Bågspår')

@section('content')
    <div class="workspace-page form-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.dashboard') }}">Projekt</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Ny båge</span>
        </nav>

        <section class="form-page__panel" aria-labelledby="page-title">
            <p class="workshop-eyebrow">NYTT ARBETSKORT</p>
            <h1 class="workshop-heading" id="page-title">Lägg till en båge</h1>
            <p class="workspace-lede">Projekt: {{ $project->name }}. Börja med en kod. Det första arbetssteget blir Mottagen.</p>

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

            <form class="workshop-form" method="POST" action="{{ route('app.sashes.store', $project) }}">
                @csrf
                <div class="form-field">
                    <label for="sash-code">Bågens kod <span class="form-required">Obligatoriskt</span></label>
                    <input id="sash-code" name="code" type="text" value="{{ is_string(old('code')) ? old('code') : '' }}" autocomplete="off" maxlength="120" required autofocus @if ($errors->has('code')) aria-invalid="true" aria-describedby="sash-code-error" @endif>
                    @error('code')
                        <p class="form-field__error" id="sash-code-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="sash-room">Rum <span class="form-optional">Valfritt</span></label>
                    <input id="sash-room" name="room" type="text" value="{{ is_string(old('room')) ? old('room') : '' }}" autocomplete="off" maxlength="120" @if ($errors->has('room')) aria-invalid="true" aria-describedby="sash-room-error" @endif>
                    @error('room')
                        <p class="form-field__error" id="sash-room-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="sash-location">Placering <span class="form-optional">Valfritt</span></label>
                    <input id="sash-location" name="location" type="text" value="{{ is_string(old('location')) ? old('location') : '' }}" autocomplete="off" maxlength="160" @if ($errors->has('location')) aria-invalid="true" aria-describedby="sash-location-error" @endif>
                    @error('location')
                        <p class="form-field__error" id="sash-location-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="sash-notes">Anteckningar <span class="form-optional">Valfritt</span></label>
                    <textarea id="sash-notes" name="notes" rows="4" maxlength="3000" @if ($errors->has('notes')) aria-invalid="true" aria-describedby="sash-notes-error" @endif>{{ is_string(old('notes')) ? old('notes') : '' }}</textarea>
                    @error('notes')
                        <p class="form-field__error" id="sash-notes-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-actions">
                    <button class="workshop-button workshop-button--primary" type="submit">Skapa arbetskort</button>
                    <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Avbryt</a>
                </div>
            </form>
        </section>
    </div>
@endsection
