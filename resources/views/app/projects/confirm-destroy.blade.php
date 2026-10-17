@extends('layouts.workshop')

@section('title', 'Ta bort '.$project->name.' — Bågspår')

@section('content')
    <section class="auth-page auth-page--wide" aria-labelledby="page-title">
        <p class="workshop-eyebrow">KAN INTE ÅNGRAS</p>
        <h1 id="page-title" class="workshop-heading">Ta bort {{ $project->name }}?</h1>
        <p class="auth-page__intro">Bågarna, historiken och bildfilerna tas bort. Det går inte att ångra. Arkivering döljer projektet men sparar uppgifterna.</p>
        <div class="project-danger-actions">
            <form method="POST" action="{{ route('app.projects.archive', $project) }}">
                @csrf
                <input type="hidden" name="action" value="archive">
                <button class="workshop-button workshop-button--primary" type="submit">Arkivera i stället</button>
            </form>
            <form method="POST" action="{{ route('app.projects.destroy', $project) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirmation" value="ta-bort">
                <button class="workshop-button workshop-button--danger" type="submit">Ta bort projektet och bilderna</button>
            </form>
            <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Avbryt</a>
        </div>
    </section>
@endsection
