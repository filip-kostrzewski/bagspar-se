@extends('layouts.workshop')

@section('title', $sash->code.' — '.$project->name.' — Bågspår')

@section('content')
    @php
        $stageDefinitions = [
            'received' => 'Mottagen',
            'scraping' => 'Skrapning',
            'repair' => 'Trälagning',
            'glazing' => 'Glasning',
            'painting' => 'Målning',
            'ready' => 'Klar',
        ];
        $currentStageName = $stageDefinitions[$sash->stage] ?? $stageDefinitions['received'];
        $workCardUrl = route('app.sashes.show', [$project, $sash]);
    @endphp
    <div class="workspace-page sash-detail-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.dashboard') }}">Projekt</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $sash->code }}</span>
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

        <section class="sash-hero" aria-labelledby="page-title">
            <div class="sash-hero__main">
                <p class="workshop-eyebrow">ARBETSKORT · {{ $project->name }}</p>
                <h1 class="workshop-heading" id="page-title">Båge {{ $sash->code }}</h1>
                <div class="sash-hero__location">
                    @if ($sash->room || $sash->location)
                        <span>{{ collect([$sash->room, $sash->location])->filter()->join(' · ') }}</span>
                    @else
                        <span class="text-muted">Plats inte angiven</span>
                    @endif
                    <span class="sash-hero__current"><span class="status-dot" aria-hidden="true"></span> {{ $currentStageName }}</span>
                </div>
            </div>
            <div class="sash-hero__actions no-print">
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.sashes.label', [$project, $sash]) }}">Skriv ut etikett</a>
            </div>
        </section>

        <div class="sash-workspace">
            <details class="stage-panel" aria-labelledby="stage-title" open data-stage-panel>
                <summary class="stage-panel__summary">
                    <span>
                        <span class="workshop-eyebrow">FRÅN VERKSTAD TILL ÅTERMONTERING</span>
                        <span
                            class="workshop-section-heading"
                            id="stage-title"
                            role="heading"
                            aria-level="2"
                        >Arbetssteg</span>
                    </span>
                    <span class="stage-panel__summary-meta">
                        <span class="stage-panel__status">{{ $currentStageName }}</span>
                        <span class="stage-panel__toggle" aria-hidden="true"></span>
                    </span>
                </summary>
                @include('partials.workshop.stage-tracker', ['currentStage' => $sash->stage])
            </details>

            <section class="stage-update-panel" aria-labelledby="update-title">
                <p class="workshop-eyebrow">NÄSTA MOMENT</p>
                <h2 class="workshop-section-heading" id="update-title">Uppdatera arbetskort</h2>
                <form class="workshop-form" method="POST" action="{{ route('app.sashes.update', [$project, $sash]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="form_context" value="sash">
                    <div class="form-field">
                        <label for="stage">Aktuellt steg</label>
                        <select id="stage" name="stage" required @if ($errors->has('stage')) aria-invalid="true" aria-describedby="stage-error" @endif>
                            @foreach ($stageDefinitions as $stageKey => $stageName)
                        <option value="{{ $stageKey }}" @selected(old('form_context') === 'sash' && is_string(old('stage')) ? old('stage') === $stageKey : $sash->stage === $stageKey)>{{ $stageName }}</option>
                            @endforeach
                        </select>
                        @error('stage')
                            @if (old('form_context') === 'sash')
                                <p class="form-field__error" id="stage-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="notes">Arbetsanteckningar</label>
                        <textarea id="notes" name="notes" rows="5" maxlength="3000" @if ($errors->has('notes') && old('form_context') === 'sash') aria-invalid="true" aria-describedby="sash-notes-error" @endif>{{ old('form_context') === 'sash' && is_string(old('notes')) ? old('notes') : $sash->notes }}</textarea>
                        @error('notes')
                            @if (old('form_context') === 'sash')
                                <p class="form-field__error" id="sash-notes-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <details class="sash-form-details" @if (old('form_context') === 'sash' && ($errors->has('code') || $errors->has('room') || $errors->has('location'))) open @endif>
                        <summary>Ändra kod eller placering</summary>
                        <p>Rätta märkningen eller platsen för den här bågen.</p>
                        <div class="form-field">
                            <label for="code">Bågens kod</label>
                            <input id="code" name="code" type="text" value="{{ old('form_context') === 'sash' && is_string(old('code')) ? old('code') : $sash->code }}" maxlength="120" required @if ($errors->has('code') && old('form_context') === 'sash') aria-invalid="true" aria-describedby="code-error" @endif>
                            @error('code')
                                @if (old('form_context') === 'sash')
                                    <p class="form-field__error" id="code-error">{{ $message }}</p>
                                @endif
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="room">Rum</label>
                            <input id="room" name="room" type="text" value="{{ old('form_context') === 'sash' && is_string(old('room')) ? old('room') : $sash->room }}" maxlength="120" @if ($errors->has('room') && old('form_context') === 'sash') aria-invalid="true" aria-describedby="room-error" @endif>
                            @error('room')
                                @if (old('form_context') === 'sash')
                                    <p class="form-field__error" id="room-error">{{ $message }}</p>
                                @endif
                            @enderror
                        </div>
                        <div class="form-field">
                            <label for="location">Placering</label>
                            <input id="location" name="location" type="text" value="{{ old('form_context') === 'sash' && is_string(old('location')) ? old('location') : $sash->location }}" maxlength="160" @if ($errors->has('location') && old('form_context') === 'sash') aria-invalid="true" aria-describedby="location-error" @endif>
                            @error('location')
                                @if (old('form_context') === 'sash')
                                    <p class="form-field__error" id="location-error">{{ $message }}</p>
                                @endif
                            @enderror
                        </div>
                    </details>
                    <button class="workshop-button workshop-button--primary" type="submit">Spara arbetskort</button>
                </form>
            </section>

            <section class="sash-history" aria-labelledby="history-title">
                <p class="workshop-eyebrow">VAD SOM HÄNT</p>
                <h2 class="workshop-section-heading" id="history-title">Historik</h2>
                @if ($events->isEmpty())
                    <p class="sash-history__empty">Inga steg eller anteckningar är sparade i historiken ännu.</p>
                @else
                    <ol class="sash-history__list">
                        @foreach ($events as $event)
                            <li class="sash-history__item">
                                <p class="sash-history__meta">
                                    <time datetime="{{ $event->created_at?->toAtomString() }}">{{ $event->created_at?->timezone('Europe/Stockholm')->format('Y-m-d H:i') }}</time>
                                    <span>{{ $event->user_name }}</span>
                                </p>
                                @if ($event->kind === 'stage')
                                    <p>Steg: @include('partials.workshop.stage-label', ['stage' => $event->stage])</p>
                                @elseif (is_string($event->body) && $event->body !== '')
                                    <p class="sash-history__note">{{ $event->body }}</p>
                                @else
                                    <p>Anteckningen togs bort.</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            <section class="work-card-link-panel" aria-labelledby="link-title">
                <p class="workshop-eyebrow">SKANNA FÖR ARBETSKORTET</p>
                <h2 class="workshop-section-heading" id="link-title">QR-kod för den här bågen</h2>
                <div class="work-card-qr">
                    <img
                        src="{{ route('app.sashes.qr', [$project, $sash]) }}"
                        alt="QR-kod till arbetskort {{ $sash->code }}"
                        width="160"
                        height="160"
                    >
                    <div class="work-card-qr__details">
                        <strong>{{ $sash->code }}</strong>
                        <span>{{ $project->name }}</span>
                        @if ($sash->room || $sash->location)
                            <span>{{ collect([$sash->room, $sash->location])->filter()->join(' · ') }}</span>
                        @endif
                        <p>Skanningen är till för verkstadens personal och kräver inloggning. Det finns ingen publik vy.</p>
                    </div>
                </div>
                <p class="work-card-link-panel__fallback">Om QR-koden inte kan läsas kan du kopiera länken:</p>
                <div class="copy-link-control">
                    <label class="visually-hidden" for="work-card-url">Arbetskortets länk</label>
                    <input id="work-card-url" type="url" value="{{ $workCardUrl }}" readonly data-copy-value>
                    <button class="workshop-button workshop-button--quiet" type="button" data-copy-link="work-card-url">Kopiera</button>
                </div>
                <p class="copy-link-control__status" aria-live="polite" data-copy-status></p>
            </section>

            <section class="photo-section" aria-labelledby="photos-title">
            <div class="section-heading-row">
                <div>
                    <p class="workshop-eyebrow">BILDER FÖR DEN HÄR BÅGEN</p>
                    <h2 class="workshop-section-heading" id="photos-title">Fotodokumentation <span>{{ $photos->count() }}</span></h2>
                </div>
            </div>

            @if ($photos->isEmpty())
                <div class="photo-empty">
                    <span class="photo-empty__icon" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="8" width="30" height="25" rx="2"/><circle cx="14" cy="16" r="3"/><path d="m7 30 9-8 5 4 5-6 7 8"/></svg>
                    </span>
                    <p>Här samlas bilderna för just den här bågen.</p>
                </div>
            @else
                <div class="photo-grid">
                    @foreach ($photos as $photo)
                        <figure class="photo-card">
                            <a class="photo-card__image-link" href="{{ route('app.sashes.photos.show', [$project, $sash, $photo]) }}" target="_blank" rel="noopener" aria-label="Öppna bilden {{ $photo->original_filename ?? 'i ett nytt fönster' }}">
                                <img src="{{ route('app.sashes.photos.show', [$project, $sash, $photo]) }}" alt="{{ $photo->original_filename ?? 'Foto' }} för båge {{ $sash->code }}" loading="lazy">
                            </a>
                            <figcaption>
                                <strong>{{ $photo->original_filename ?? 'Foto' }}</strong>
                                <span>{{ $photo->created_at?->timezone('Europe/Stockholm')->format('Y-m-d') }}</span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif

            @if ($photos->count() >= 20)
                <div class="photo-upload-panel photo-upload-panel--limit">
                    <div>
                        <h3>Fotodokumentationen är full</h3>
                        <p>Du har nått gränsen på 20 bilder för den här bågen.</p>
                    </div>
                </div>
            @else
            <div class="photo-upload-panel">
                <div>
                    <h3>Lägg till en bild</h3>
                    <p>JPG, PNG eller WebP. Högst 5 MB, 16 megapixlar och 6 000 pixlar per sida. Upp till 20 bilder per båge.</p>
                </div>
                <form class="workshop-form photo-upload-form" method="POST" action="{{ route('app.sashes.photos.store', [$project, $sash]) }}" enctype="multipart/form-data" data-photo-upload>
                    @csrf
                    <input type="hidden" name="form_context" value="photo">
                    <div class="form-field">
                        <label for="moment">Tillfälle</label>
                        <select id="moment" name="moment" required @if ($errors->has('moment') && old('form_context') === 'photo') aria-invalid="true" aria-describedby="moment-error" @endif>
                            <option value="" @selected(! in_array(old('moment'), ['before', 'after'], true))>Välj före eller efter</option>
                            <option value="before" @selected(old('moment') === 'before')>Före</option>
                            <option value="after" @selected(old('moment') === 'after')>Efter</option>
                        </select>
                        @error('moment')
                            @if (old('form_context') === 'photo')
                                <p class="form-field__error" id="moment-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label class="visually-hidden" for="photo">Bildfil</label>
                        <div class="file-picker">
                            <input id="photo" class="visually-hidden" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required @if ($errors->has('photo')) aria-invalid="true" aria-describedby="photo-error" @endif>
                            <button class="workshop-button workshop-button--secondary" type="button" data-file-picker="photo">Välj bild</button>
                            <span class="file-picker__name" data-file-name>Ingen bild vald</span>
                        </div>
                        @error('photo')
                            @if (old('form_context') === 'photo')
                                <p class="form-field__error" id="photo-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="photo-preview" data-photo-preview aria-live="polite"></div>
                    <p class="photo-upload-status" role="status" data-photo-upload-message></p>
                    <button class="workshop-button workshop-button--secondary" type="submit">Ladda upp bild</button>
                </form>
            </div>
            @endif
            </section>
        </div>
    </div>
@endsection
