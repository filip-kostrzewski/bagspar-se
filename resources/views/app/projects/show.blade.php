@extends('layouts.workshop')

@section('title', $project->name.' — Bågspår')

@section('content')
    @php
        $projectIsComplete = $project->status === 'completed';
        $sashCount = $sashes->count();
    @endphp
    <div class="workspace-page project-detail-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.dashboard') }}">Projekt</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $project->name }}</span>
        </nav>

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

        <section class="project-hero" aria-labelledby="page-title">
            <div class="project-hero__main">
                <span class="project-card__status @if ($projectIsComplete) project-card__status--complete @endif">
                    <span class="status-dot" aria-hidden="true"></span>
                    {{ $projectIsComplete ? 'Avslutat projekt' : 'Pågående projekt' }}
                </span>
                <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
                <div class="project-hero__facts">
                    @if ($project->customer_name)
                        <p><span>Kund</span>{{ $project->customer_name }}</p>
                    @endif
                    @if ($project->location)
                        <p><span>Plats</span>{{ $project->location }}</p>
                    @endif
                    <p><span>Arbetskort</span>{{ $sashCount }} {{ $sashCount === 1 ? 'båge' : 'bågar' }}</p>
                </div>
                @if ($project->notes)
                    <div class="project-notes">
                        <h2>Projektanteckningar</h2>
                        <p>{{ $project->notes }}</p>
                    </div>
                @endif
            </div>
            <div class="project-hero__actions no-print">
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.report', $project) }}">Visa projektrapport</a>
                <form method="POST" action="{{ route('app.projects.update', $project) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="form_context" value="status">
                    <input type="hidden" name="status" value="{{ $projectIsComplete ? 'active' : 'completed' }}">
                    <button class="workshop-button workshop-button--quiet" type="submit">{{ $projectIsComplete ? 'Återöppna projekt' : 'Markera som avslutat' }}</button>
                </form>
            </div>
        </section>

        <details class="edit-details-panel" @if (old('form_context') === 'project') open @endif>
            <summary>Redigera projektuppgifter</summary>
            <div class="edit-details-panel__body">
                <p class="workshop-eyebrow">KORRIGERA PROJEKTET</p>
                <h2 class="workshop-section-heading">Projektuppgifter</h2>
                <form class="workshop-form project-form" method="POST" action="{{ route('app.projects.update', $project) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="form_context" value="project">
                    <div class="form-field">
                        <label for="edit-project-name">Projektnamn</label>
                        <input id="edit-project-name" name="name" type="text" value="{{ old('form_context') === 'project' && is_string(old('name')) ? old('name') : $project->name }}" maxlength="160" required @if ($errors->has('name') && old('form_context') === 'project') aria-invalid="true" aria-describedby="edit-project-name-error" @endif>
                        @error('name')
                            @if (old('form_context') === 'project')
                                <p class="form-field__error" id="edit-project-name-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="edit-project-customer">Kund eller beställare</label>
                        <input id="edit-project-customer" name="customer_name" type="text" value="{{ old('form_context') === 'project' && is_string(old('customer_name')) ? old('customer_name') : $project->customer_name }}" maxlength="160" @if ($errors->has('customer_name') && old('form_context') === 'project') aria-invalid="true" aria-describedby="edit-project-customer-error" @endif>
                        @error('customer_name')
                            @if (old('form_context') === 'project')
                                <p class="form-field__error" id="edit-project-customer-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="edit-project-location">Adress eller plats</label>
                        <input id="edit-project-location" name="location" type="text" value="{{ old('form_context') === 'project' && is_string(old('location')) ? old('location') : $project->location }}" maxlength="255" @if ($errors->has('location') && old('form_context') === 'project') aria-invalid="true" aria-describedby="edit-project-location-error" @endif>
                        @error('location')
                            @if (old('form_context') === 'project')
                                <p class="form-field__error" id="edit-project-location-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="edit-project-notes">Anteckningar</label>
                        <textarea id="edit-project-notes" name="notes" rows="4" maxlength="5000" @if ($errors->has('notes') && old('form_context') === 'project') aria-invalid="true" aria-describedby="edit-project-notes-error" @endif>{{ old('form_context') === 'project' && is_string(old('notes')) ? old('notes') : $project->notes }}</textarea>
                        @error('notes')
                            @if (old('form_context') === 'project')
                                <p class="form-field__error" id="edit-project-notes-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <button class="workshop-button workshop-button--secondary" type="submit">Spara projektuppgifter</button>
                </form>
            </div>
        </details>

        <section class="sash-section" aria-labelledby="sashes-title">
            <div class="section-heading-row">
                <div>
                    <p class="workshop-eyebrow">FÖNSTER FÖR FÖNSTER</p>
                    <h2 class="workshop-section-heading" id="sashes-title">Bågar <span>{{ $sashCount }}</span></h2>
                </div>
                <a class="workshop-button workshop-button--primary no-print" href="{{ route('app.sashes.create', $project) }}">Lägg till båge</a>
            </div>

            @if ($sashes->isEmpty())
                <div class="sashes-empty">
                    <span class="sashes-empty__icon" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="5" width="24" height="30" rx="1.5"/><path d="M20 5v30M8 20h24"/><circle cx="27" cy="28" r="5" fill="#F7F6F1"/><path d="M27 25.5v5M24.5 28h5"/></svg>
                    </span>
                    <h3>Projektet väntar på sin första båge.</h3>
                    <p>Skapa ett arbetskort för varje båge du vill följa genom verkstaden.</p>
                </div>
            @else
                <div class="sash-list">
                    @foreach ($sashes as $sash)
                        <article class="sash-card">
                            <a class="sash-card__main" href="{{ route('app.sashes.show', [$project, $sash]) }}">
                                <span class="sash-card__code">{{ $sash->code }}</span>
                                <span class="sash-card__details">
                                    @if ($sash->room || $sash->location)
                                        <span>{{ collect([$sash->room, $sash->location])->filter()->join(' · ') }}</span>
                                    @else
                                        <span class="text-muted">Plats inte angiven</span>
                                    @endif
                                    <span class="sash-card__stage">
                                        @include('partials.workshop.stage-icon', ['stage' => $sash->stage])
                                        @include('partials.workshop.stage-label', ['stage' => $sash->stage])
                                    </span>
                                </span>
                                <span class="sash-card__arrow" aria-hidden="true">→</span>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
@endsection
