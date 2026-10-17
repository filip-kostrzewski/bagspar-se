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
                    <div class="form-field">
                        <label for="intake-notes">Skick vid mottagning</label>
                        <p class="form-field__hint" id="intake-notes-hint">Sprickor, röta, saknade beslag, lösa rutor. Visas i mottagningsprotokollet.</p>
                        <textarea id="intake-notes" name="intake_notes" rows="3" maxlength="2000" aria-describedby="intake-notes-hint{{ $errors->has('intake_notes') && old('form_context') === 'sash' ? ' intake-notes-error' : '' }}" @if ($errors->has('intake_notes') && old('form_context') === 'sash') aria-invalid="true" @endif>{{ old('form_context') === 'sash' && is_string(old('intake_notes')) ? old('intake_notes') : $sash->intake_notes }}</textarea>
                        @error('intake_notes')
                            @if (old('form_context') === 'sash')
                                <p class="form-field__error" id="intake-notes-error">{{ $message }}</p>
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
                                    <time datetime="{{ $event->created_at?->toAtomString() }}">{{ \App\Support\SwedishDate::formatDateTime($event->created_at) }}</time>
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

            <section class="sash-log" aria-labelledby="time-title">
                <p class="workshop-eyebrow">NEDLAGD TID</p>
                <h2 class="workshop-section-heading" id="time-title">Arbetstid @if ($timeEntries->isNotEmpty())<span>{{ \App\Support\WorkDuration::label((int) $timeEntries->sum('minutes')) }}</span>@endif</h2>
                @if ($timeEntries->isEmpty())
                    <p class="sash-history__empty">Ingen tid är registrerad ännu.</p>
                @else
                    <p>Totalt {{ \App\Support\WorkDuration::label((int) $timeEntries->sum('minutes')) }} på den här bågen.</p>
                    <ul class="sash-log__list">
                        @foreach ($timeEntries as $entry)
                            @php
                                $timeContext = 'time-'.$entry->getKey();
                                $editingTime = old('form_context') === $timeContext;
                            @endphp
                            <li>
                                <span>{{ \App\Support\SwedishDate::format($entry->worked_on) }}</span>
                                <span>{{ \App\Support\WorkDuration::label($entry->minutes) }}</span>
                                <span>{{ $entry->user_name }}</span>
                                @if (is_string($entry->note) && $entry->note !== '')
                                    <p class="sash-log__note">{{ $entry->note }}</p>
                                @endif
                                @if (auth()->user()?->isOwner() || $entry->user_id === auth()->id())
                                    <details class="sash-log__edit" @if ($editingTime) open @endif>
                                        <summary>Ändra</summary>
                                        <form class="workshop-form sash-log__form" method="POST" action="{{ route('app.sashes.time.update', [$project, $sash, $entry]) }}" novalidate>
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="form_context" value="{{ $timeContext }}">
                                            <div class="form-field">
                                                <label for="worked-on-{{ $entry->getKey() }}">Datum</label>
                                                <input id="worked-on-{{ $entry->getKey() }}" name="worked_on" type="date" value="{{ $editingTime && is_string(old('worked_on')) ? old('worked_on') : $entry->worked_on?->toDateString() }}">
                                                @error('worked_on')
                                                    @if ($editingTime)
                                                        <p class="form-field__error">{{ $message }}</p>
                                                    @endif
                                                @enderror
                                            </div>
                                            <div class="form-field">
                                                <label for="hours-{{ $entry->getKey() }}">Timmar</label>
                                                <input id="hours-{{ $entry->getKey() }}" name="hours" type="text" inputmode="numeric" value="{{ $editingTime && is_string(old('hours')) ? old('hours') : (string) intdiv($entry->minutes, 60) }}">
                                                @error('hours')
                                                    @if ($editingTime)
                                                        <p class="form-field__error">{{ $message }}</p>
                                                    @endif
                                                @enderror
                                            </div>
                                            <div class="form-field">
                                                <label for="minutes-{{ $entry->getKey() }}">Minuter</label>
                                                <input id="minutes-{{ $entry->getKey() }}" name="minutes" type="text" inputmode="numeric" value="{{ $editingTime && is_string(old('minutes')) ? old('minutes') : (string) ($entry->minutes % 60) }}">
                                                @error('minutes')
                                                    @if ($editingTime)
                                                        <p class="form-field__error">{{ $message }}</p>
                                                    @endif
                                                @enderror
                                            </div>
                                            <div class="form-field">
                                                <label for="note-{{ $entry->getKey() }}">Anteckning <span class="form-optional">Valfritt</span></label>
                                                <input id="note-{{ $entry->getKey() }}" name="note" type="text" maxlength="500" value="{{ $editingTime && is_string(old('note')) ? old('note') : $entry->note }}">
                                            </div>
                                            <button class="workshop-button workshop-button--primary" type="submit">Spara ändring</button>
                                        </form>
                                    </details>
                                    <form method="POST" action="{{ route('app.sashes.time.destroy', [$project, $sash, $entry]) }}" data-confirm="Tiden tas bort från bågen." data-confirm-title="Ta bort tidsposten?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="workshop-button workshop-button--quiet" type="submit">Ta bort</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <form class="workshop-form sash-log__form" method="POST" action="{{ route('app.sashes.time.store', [$project, $sash]) }}" novalidate>
                    @csrf
                    <input type="hidden" name="form_context" value="time">
                    <div class="form-field">
                        <label for="worked_on">Datum</label>
                        <input id="worked_on" name="worked_on" type="date" value="{{ old('form_context') === 'time' && is_string(old('worked_on')) ? old('worked_on') : now()->timezone('Europe/Stockholm')->toDateString() }}" @if ($errors->has('worked_on') && old('form_context') === 'time') aria-invalid="true" aria-describedby="worked-on-error" @endif>
                        @error('worked_on')
                            @if (old('form_context') === 'time')
                                <p class="form-field__error" id="worked-on-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="hours">Timmar</label>
                        <input id="hours" name="hours" type="text" inputmode="numeric" value="{{ old('form_context') === 'time' && is_string(old('hours')) ? old('hours') : '0' }}" @if ($errors->has('hours') && old('form_context') === 'time') aria-invalid="true" aria-describedby="hours-error" @endif>
                        @error('hours')
                            @if (old('form_context') === 'time')
                                <p class="form-field__error" id="hours-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="minutes">Minuter</label>
                        <input id="minutes" name="minutes" type="text" inputmode="numeric" value="{{ old('form_context') === 'time' && is_string(old('minutes')) ? old('minutes') : '0' }}" @if ($errors->has('minutes') && old('form_context') === 'time') aria-invalid="true" aria-describedby="minutes-error" @endif>
                        @error('minutes')
                            @if (old('form_context') === 'time')
                                <p class="form-field__error" id="minutes-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="time-note">Anteckning <span class="form-optional">Valfritt</span></label>
                        <input id="time-note" name="note" type="text" maxlength="500" value="{{ old('form_context') === 'time' && is_string(old('note')) ? old('note') : '' }}">
                    </div>
                    <button class="workshop-button workshop-button--primary" type="submit">Spara tid</button>
                </form>
            </section>

            <section class="sash-materials" aria-labelledby="materials-title">
                <p class="workshop-eyebrow">DET SOM GÅTT ÅT</p>
                <h2 class="workshop-section-heading" id="materials-title">Material på den här bågen</h2>
                @if ($materials->isEmpty())
                    <p class="sash-history__empty">Inget material är registrerat på den här bågen ännu.</p>
                @else
                    <p>{{ $materials->count() === 1 ? '1 rad' : $materials->count().' rader' }} på den här bågen.</p>
                    <ul class="sash-log__list">
                        @foreach ($materials as $material)
                            @php
                                $materialContext = 'material-'.$material->getKey();
                                $editingMaterial = old('form_context') === $materialContext;
                            @endphp
                            <li>
                                <span>{{ \App\Models\SashMaterial::labelForType($material->type) }}</span>
                                <span>{{ $material->description }}</span>
                                <span>{{ \App\Models\SashMaterial::quantityLabel($material->quantity) }} {{ $material->unit }}@if (is_int($material->unit_price_ore)) · à {{ \App\Support\Kronor::format($material->unit_price_ore) }}@endif</span>
                                <details class="sash-log__edit" @if ($editingMaterial) open @endif>
                                    <summary>Ändra</summary>
                                    <form class="workshop-form sash-log__form" method="POST" action="{{ route('app.sashes.materials.update', [$project, $sash, $material]) }}" novalidate>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="form_context" value="{{ $materialContext }}">
                                        <div class="form-field">
                                            <label for="material-type-{{ $material->getKey() }}">Typ</label>
                                            <select id="material-type-{{ $material->getKey() }}" name="type" @if ($errors->has('type') && $editingMaterial) aria-invalid="true" @endif>
                                                @foreach (\App\Models\SashMaterial::TYPES as $type)
                                                    <option value="{{ $type }}" @selected(($editingMaterial ? old('type') : $material->type) === $type)>{{ \App\Models\SashMaterial::labelForType($type) }}</option>
                                                @endforeach
                                            </select>
                                            @error('type')
                                                @if ($editingMaterial)
                                                    <p class="form-field__error">{{ $message }}</p>
                                                @endif
                                            @enderror
                                        </div>
                                        <div class="form-field">
                                            <label for="material-description-{{ $material->getKey() }}">Beskrivning</label>
                                            <input id="material-description-{{ $material->getKey() }}" name="description" type="text" maxlength="160" value="{{ $editingMaterial && is_string(old('description')) ? old('description') : $material->description }}" @if ($errors->has('description') && $editingMaterial) aria-invalid="true" @endif>
                                            @error('description')
                                                @if ($editingMaterial)
                                                    <p class="form-field__error">{{ $message }}</p>
                                                @endif
                                            @enderror
                                        </div>
                                        <div class="form-field">
                                            <label for="material-quantity-{{ $material->getKey() }}">Mängd</label>
                                            <input id="material-quantity-{{ $material->getKey() }}" name="quantity" type="text" inputmode="decimal" value="{{ $editingMaterial && is_string(old('quantity')) ? old('quantity') : \App\Models\SashMaterial::quantityLabel($material->quantity) }}" @if ($errors->has('quantity') && $editingMaterial) aria-invalid="true" @endif>
                                            @error('quantity')
                                                @if ($editingMaterial)
                                                    <p class="form-field__error">{{ $message }}</p>
                                                @endif
                                            @enderror
                                        </div>
                                        <div class="form-field">
                                            <label for="material-unit-{{ $material->getKey() }}">Enhet</label>
                                            <input id="material-unit-{{ $material->getKey() }}" name="unit" type="text" maxlength="20" value="{{ $editingMaterial && is_string(old('unit')) ? old('unit') : $material->unit }}" @if ($errors->has('unit') && $editingMaterial) aria-invalid="true" @endif>
                                            @error('unit')
                                                @if ($editingMaterial)
                                                    <p class="form-field__error">{{ $message }}</p>
                                                @endif
                                            @enderror
                                        </div>
                                        <div class="form-field">
                                            <label for="material-price-{{ $material->getKey() }}">À-pris (kr) <span class="form-optional">Valfritt</span></label>
                                            <input id="material-price-{{ $material->getKey() }}" name="unit_price" type="text" inputmode="decimal" value="{{ $editingMaterial && is_string(old('unit_price')) ? old('unit_price') : ($material->unit_price_ore !== null ? \App\Support\Kronor::input($material->unit_price_ore) : '') }}" @if ($errors->has('unit_price') && $editingMaterial) aria-invalid="true" @endif>
                                            @error('unit_price')
                                                @if ($editingMaterial)
                                                    <p class="form-field__error">{{ $message }}</p>
                                                @endif
                                            @enderror
                                        </div>
                                        <button class="workshop-button workshop-button--primary" type="submit">Spara ändring</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('app.sashes.materials.destroy', [$project, $sash, $material]) }}" data-confirm="Materialraden tas bort från bågen." data-confirm-title="Ta bort materialet?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="workshop-button workshop-button--quiet" type="submit">Ta bort</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <form class="workshop-form sash-log__form" method="POST" action="{{ route('app.sashes.materials.store', [$project, $sash]) }}" novalidate>
                    @csrf
                    <input type="hidden" name="form_context" value="material">
                    <div class="form-field">
                        <label for="material-type">Typ</label>
                        <select id="material-type" name="type" @if ($errors->has('type') && old('form_context') === 'material') aria-invalid="true" aria-describedby="material-type-error" @endif>
                            @foreach (\App\Models\SashMaterial::TYPES as $type)
                                <option value="{{ $type }}" @selected((old('form_context') === 'material' ? old('type') : 'glas') === $type)>{{ \App\Models\SashMaterial::labelForType($type) }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            @if (old('form_context') === 'material')
                                <p class="form-field__error" id="material-type-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="material-description">Beskrivning</label>
                        <input id="material-description" name="description" type="text" maxlength="160" value="{{ old('form_context') === 'material' && is_string(old('description')) ? old('description') : '' }}" @if ($errors->has('description') && old('form_context') === 'material') aria-invalid="true" aria-describedby="material-description-error" @endif>
                        @error('description')
                            @if (old('form_context') === 'material')
                                <p class="form-field__error" id="material-description-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="material-quantity">Mängd</label>
                        <input id="material-quantity" name="quantity" type="text" inputmode="decimal" value="{{ old('form_context') === 'material' && is_string(old('quantity')) ? old('quantity') : '' }}" @if ($errors->has('quantity') && old('form_context') === 'material') aria-invalid="true" aria-describedby="material-quantity-error" @endif>
                        @error('quantity')
                            @if (old('form_context') === 'material')
                                <p class="form-field__error" id="material-quantity-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="material-unit">Enhet</label>
                        <input id="material-unit" name="unit" type="text" maxlength="20" value="{{ old('form_context') === 'material' && is_string(old('unit')) ? old('unit') : '' }}" @if ($errors->has('unit') && old('form_context') === 'material') aria-invalid="true" aria-describedby="material-unit-error" @endif>
                        @error('unit')
                            @if (old('form_context') === 'material')
                                <p class="form-field__error" id="material-unit-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="form-field">
                        <label for="material-price">À-pris (kr) <span class="form-optional">Valfritt</span></label>
                        <input id="material-price" name="unit_price" type="text" inputmode="decimal" value="{{ old('form_context') === 'material' && is_string(old('unit_price')) ? old('unit_price') : '' }}" @if ($errors->has('unit_price') && old('form_context') === 'material') aria-invalid="true" aria-describedby="material-price-error" @endif>
                        <p class="form-field__hint">Används i ROT-underlaget. Lämna tomt om priset inte är satt.</p>
                        @error('unit_price')
                            @if (old('form_context') === 'material')
                                <p class="form-field__error" id="material-price-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <button class="workshop-button workshop-button--primary" type="submit">Spara material</button>
                </form>
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
                        <p>Skanningen är till för verkstadens personal och kräver inloggning. Kundens status är en separat länk som slås på för projektet.</p>
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
                                <p class="photo-card__caption">{{ $photo->documentCaption() }}</p>
                                <form method="POST" action="{{ route('app.sashes.photos.update', [$project, $sash, $photo]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <fieldset class="moment-choice">
                                        <legend class="visually-hidden">Tillfälle för bilden</legend>
                                        <label for="moment-{{ $photo->getKey() }}-before">
                                            <input type="radio" id="moment-{{ $photo->getKey() }}-before" value="before" name="moment" @checked($photo->moment !== 'after') required>
                                            <span>Före</span>
                                        </label>
                                        <label for="moment-{{ $photo->getKey() }}-after">
                                            <input type="radio" id="moment-{{ $photo->getKey() }}-after" value="after" name="moment" @checked($photo->moment === 'after')>
                                            <span>Efter</span>
                                        </label>
                                    </fieldset>
                                    <button class="workshop-button workshop-button--quiet" type="submit">Spara tillfälle</button>
                                </form>
                                <form method="POST" action="{{ route('app.sashes.photos.destroy', [$project, $sash, $photo]) }}" data-confirm="Filen tas bort från lagringen." data-confirm-title="Ta bort bilden?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="workshop-button workshop-button--quiet" type="submit">Ta bort bild</button>
                                </form>
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
                    <h3>Lägg till bilder</h3>
                    <p>JPG, PNG eller WebP. Högst 5 MB, 12 megapixlar och 6 000 pixlar per sida. Upp till 20 bilder per båge. Du kan välja flera bilder på en gång.</p>
                </div>
                <form class="workshop-form photo-upload-form" method="POST" action="{{ route('app.sashes.photos.store', [$project, $sash]) }}" enctype="multipart/form-data" data-photo-upload data-photo-remaining="{{ max(0, 20 - $photos->count()) }}">
                    @csrf
                    <input type="hidden" name="form_context" value="photo">
                    @php
                        $hasBeforePhoto = $photos->contains(fn ($existingPhoto): bool => $existingPhoto->moment !== 'after');
                        $oldMoment = old('form_context') === 'photo' ? old('moment') : null;
                        $selectedMoment = in_array($oldMoment, ['before', 'after'], true)
                            ? $oldMoment
                            : ($hasBeforePhoto ? 'after' : 'before');
                    @endphp
                    <fieldset class="moment-choice">
                        <legend>Tillfälle</legend>
                        <label for="moment-before">
                            <input id="moment-before" name="moment" type="radio" value="before" required @checked($selectedMoment === 'before') @if ($errors->has('moment') && old('form_context') === 'photo') aria-invalid="true" aria-describedby="moment-error" @endif>
                            <span>Före</span>
                        </label>
                        <label for="moment-after">
                            <input id="moment-after" name="moment" type="radio" value="after" @checked($selectedMoment === 'after')>
                            <span>Efter</span>
                        </label>
                        @error('moment')
                            @if (old('form_context') === 'photo')
                                <p class="form-field__error" id="moment-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </fieldset>
                    <div class="form-field">
                        <label class="visually-hidden" for="photo">Bildfiler</label>
                        <div class="file-picker">
                            <input id="photo" class="visually-hidden" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required @if ($errors->has('photos') || $errors->has('photos.*')) aria-invalid="true" aria-describedby="photo-error" @endif>
                            <button class="workshop-button workshop-button--secondary" type="button" data-file-picker="photo">Välj bilder</button>
                            <span class="file-picker__name" data-file-name>Ingen bild vald</span>
                        </div>
                        @if (old('form_context') === 'photo' && ($errors->has('photos') || $errors->has('photos.*')))
                            <p class="form-field__error" id="photo-error">{{ $errors->first('photos') ?: $errors->first('photos.*') }}</p>
                        @endif
                    </div>
                    <div class="photo-preview" data-photo-preview aria-live="polite"></div>
                    <p class="photo-upload-status" role="status" data-photo-upload-message></p>
                    <button class="workshop-button workshop-button--secondary" type="submit">Ladda upp bilder</button>
                </form>
            </div>
            @endif
            </section>

            @if (auth()->user()?->isOwner())
                <section class="project-danger-zone" aria-labelledby="sash-danger-title">
                    <h2 class="workshop-section-heading" id="sash-danger-title">Ta bort båge</h2>
                    <p>Borttagning raderar bågen och bildfilerna. Det går inte att ångra.</p>
                    <form method="POST" action="{{ route('app.sashes.destroy', [$project, $sash]) }}" data-confirm="Ta bort bågen {{ $sash->code }}? Bildfilerna tas bort." data-confirm-title="Ta bort bågen?">
                        @csrf
                        @method('DELETE')
                        <button class="workshop-button workshop-button--danger" type="submit">Ta bort båge</button>
                    </form>
                </section>
            @endif
        </div>
    </div>
@endsection
