@extends('layouts.workshop')

@section('title', 'Ta bort '.$project->name.' — Bågspår')

@section('content')
    <section class="project-danger-zone auth-page auth-page--wide" aria-labelledby="page-title">
        <p class="workshop-eyebrow">KAN INTE ÅNGRAS</p>
        <h1 id="page-title" class="workshop-heading">Ta bort {{ $project->name }}?</h1>
        <p class="auth-page__intro">Bågarna, historiken och bildfilerna tas bort. Det går inte att ångra. Arkivering döljer projektet men sparar uppgifterna.</p>
        <div class="project-danger-actions">
            <form method="POST" action="{{ route('app.projects.archive', $project) }}" data-confirm="Arkivera projektet? Det döljs i den vanliga listan." data-confirm-title="Arkivera projektet?">
                @csrf
                <input type="hidden" name="action" value="archive">
                <button class="workshop-button workshop-button--primary" type="submit">Arkivera i stället</button>
            </form>
            <form class="workshop-form" method="POST" action="{{ route('app.projects.destroy', $project) }}" data-confirm="Bågarna, historiken och bildfilerna tas bort." data-confirm-title="Ta bort projektet?" data-confirm-match="ta-bort" data-confirm-match-message="Skriv ta-bort för att bekräfta.">
                @csrf
                @method('DELETE')
                <div class="form-field">
                    <label for="confirm-delete">Skriv ta-bort för att bekräfta.</label>
                    <input id="confirm-delete" name="confirmation" type="text" value="{{ is_string(old('confirmation')) ? old('confirmation') : '' }}" autocomplete="off" required @if ($errors->has('confirmation')) aria-invalid="true" aria-describedby="confirm-delete-error" @endif>
                    <p class="form-field__error" id="confirm-delete-error" data-confirm-error @if (! $errors->has('confirmation')) hidden @endif>@error('confirmation'){{ $message }}@enderror</p>
                </div>
                <button class="workshop-button workshop-button--danger" type="submit">Ta bort projektet och bilderna</button>
            </form>
            <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Avbryt</a>
        </div>
    </section>
@endsection
