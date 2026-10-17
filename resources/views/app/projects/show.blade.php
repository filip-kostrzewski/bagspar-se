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
                <span class="project-card__status @if ($project->isArchived()) project-card__status--archived @elseif ($projectIsComplete) project-card__status--complete @endif">
                    <span class="status-dot" aria-hidden="true"></span>
                    {{ $project->isArchived() ? 'Arkiverad' : ($projectIsComplete ? 'Avslutat projekt' : 'Pågående projekt') }}
                </span>
                <h1 class="workshop-heading" id="page-title">{{ $project->name }}</h1>
                <div class="project-hero__facts">
                    @if ($project->customerLabel())
                        <p><span>Kund</span>{{ $project->customerLabel() }}</p>
                    @endif
                    @if ($project->customer?->contact_name)
                        <p><span>Kontakt</span>{{ $project->customer->contact_name }}</p>
                    @endif
                    @if ($project->customer?->phone)
                        <p><span>Telefon</span>@include('partials.workshop.phone-link', ['phone' => $project->customer->phone, 'href' => $project->customer->phoneHref()])</p>
                    @endif
                    @if ($project->location)
                        <p><span>Plats</span>{{ $project->location }}</p>
                    @endif
                    @if ($project->delivery_date)
                        <p><span>Beräknad leverans</span>{{ \App\Support\SwedishDate::format($project->delivery_date) }}</p>
                    @endif
                    @if ($project->delivery_approved_at)
                        <p class="project-approval"><span>Leverans godkänd</span>{{ \App\Support\SwedishDate::formatDateTime($project->delivery_approved_at) }}@if (is_string($project->delivery_approved_name) && $project->delivery_approved_name !== '') av {{ $project->delivery_approved_name }}@endif</p>
                        @if (auth()->user()?->isOwner())
                            <form method="POST" action="{{ route('app.projects.delivery-approval.destroy', $project) }}" data-confirm="Kundens godkännande tas bort från projektet och rapporten." data-confirm-title="Ta bort godkännandet?">
                                @csrf
                                @method('DELETE')
                                <button class="workshop-button workshop-button--quiet" type="submit">Ta bort godkännande</button>
                            </form>
                        @endif
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
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.rot', $project) }}">ROT-underlag</a>
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.labels', $project) }}">Skriv ut etiketter</a>
                <form method="POST" action="{{ route('app.projects.update', $project) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="form_context" value="status">
                    <input type="hidden" name="status" value="{{ $projectIsComplete ? 'active' : 'completed' }}">
                    <button class="workshop-button workshop-button--quiet" type="submit">{{ $projectIsComplete ? 'Återöppna projekt' : 'Markera som avslutat' }}</button>
                </form>
                @if (auth()->user()?->isOwner())
                    <form method="POST" action="{{ route('app.projects.archive', $project) }}" data-confirm="{{ $project->isArchived() ? 'Återställ projektet till den vanliga listan?' : 'Arkivera projektet? Det döljs i den vanliga listan.' }}" data-confirm-title="{{ $project->isArchived() ? 'Återställ projektet?' : 'Arkivera projektet?' }}">
                        @csrf
                        <input type="hidden" name="action" value="{{ $project->isArchived() ? 'restore' : 'archive' }}">
                        <button class="workshop-button workshop-button--primary" type="submit">{{ $project->isArchived() ? 'Återställ projekt' : 'Arkivera projekt' }}</button>
                    </form>
                @endif
            </div>
        </section>
        @if ($project->isArchived())
            <p class="project-archived-note">Projektet är arkiverat och visas inte i den vanliga listan.</p>
        @endif

        <section class="client-status-share" aria-labelledby="client-status-title">
            <p class="workshop-eyebrow">TILL KUNDEN</p>
            <h2 class="workshop-section-heading" id="client-status-title">Statuslänk</h2>
            <p>Länken visar verkstadens namn och kontakt, bågarnas etapp och bilder före och efter. Anteckningar följer inte med. QR-koden på etiketten är fortfarande bara för personalen.</p>
            @if ($project->clientStatusIsPublic())
                <div class="copy-link-control">
                    <label class="visually-hidden" for="client-status-url">Kundens statuslänk</label>
                    <input id="client-status-url" type="url" value="{{ route('client-status.show', ['token' => $project->client_status_token]) }}" readonly>
                    <button class="workshop-button workshop-button--quiet" type="button" data-copy-link="client-status-url">Kopiera</button>
                </div>
                <p class="copy-link-control__status" aria-live="polite" data-copy-status></p>
                <div class="client-status-share__actions">
                    <form method="POST" action="{{ route('app.projects.client-status.update', $project) }}">
                        @csrf
                        <input type="hidden" name="action" value="rotate">
                        <button class="workshop-button workshop-button--quiet" type="submit">Skapa ny länk</button>
                    </form>
                    <form method="POST" action="{{ route('app.projects.client-status.update', $project) }}">
                        @csrf
                        <input type="hidden" name="action" value="disable">
                        <button class="workshop-button workshop-button--secondary" type="submit">Stäng av statuslänk</button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('app.projects.client-status.update', $project) }}">
                    @csrf
                    <input type="hidden" name="action" value="enable">
                    <button class="workshop-button workshop-button--secondary" type="submit">Slå på statuslänk</button>
                </form>
            @endif
        </section>

        @php
            $quote = $project->quote;
        @endphp
        <section class="client-status-share quote-share" aria-labelledby="quote-title">
            <p class="workshop-eyebrow">TILL KUNDEN</p>
            <h2 class="workshop-section-heading" id="quote-title">Offert</h2>
            @if ($quote?->isAccepted())
                <p class="quote-share__state">Godkänd {{ \App\Support\SwedishDate::formatDateTime($quote->accepted_at) }} av {{ $quote->accepted_name }}. Summa {{ \App\Support\Kronor::format($quote->totalOre()) }} inkl. moms.</p>
            @elseif ($quote?->isSent())
                <p class="quote-share__state">Skickad {{ \App\Support\SwedishDate::formatDateTime($quote->sent_at) }}. Summa {{ \App\Support\Kronor::format($quote->totalOre()) }} inkl. moms.</p>
                <div class="copy-link-control">
                    <label class="visually-hidden" for="quote-url">Länk till offerten</label>
                    <input id="quote-url" type="url" value="{{ route('quote.show', ['token' => $quote->token]) }}" readonly>
                    <button class="workshop-button workshop-button--quiet" type="button" data-copy-link="quote-url">Kopiera</button>
                </div>
                <p class="copy-link-control__status" aria-live="polite" data-copy-status></p>
            @else
                <p class="quote-share__state">Utkast. Skriv offerten rad för rad och skicka länken till kunden, som godkänner med sitt namn.</p>
            @endif
            <div class="client-status-share__actions">
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.quote.edit', $project) }}">{{ $quote === null || $quote->isDraft() ? 'Skriv offert' : ($quote->isAccepted() ? 'Visa offerten' : 'Öppna offerten') }}</a>
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
                    @include('partials.workshop.customer-fields', [
                        'prefix' => 'edit-',
                        'customers' => $customers,
                        'selectedCustomerId' => old('form_context') === 'project' ? old('customer_id') : $project->customer_id,
                    ])
                    <div class="form-field">
                        <label for="edit-delivery-date">Beräknad leverans</label>
                        <input id="edit-delivery-date" name="delivery_date" type="date" value="{{ old('form_context') === 'project' ? (is_string(old('delivery_date')) ? old('delivery_date') : '') : ($project->delivery_date?->toDateString() ?? '') }}" @if ($errors->has('delivery_date') && old('form_context') === 'project') aria-invalid="true" aria-describedby="edit-delivery-date-error" @endif>
                        @error('delivery_date')
                            @if (old('form_context') === 'project')
                                <p class="form-field__error" id="edit-delivery-date-error">{{ $message }}</p>
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
                    <div class="form-field">
                        <label class="workshop-checkbox" for="edit-notify-customer-when-ready">
                            <input id="edit-notify-customer-when-ready" name="notify_customer_when_ready" type="checkbox" value="1" @checked(old('form_context') === 'project' ? old('notify_customer_when_ready') : $project->notify_customer_when_ready)>
                            <span>Mejla kunden när alla bågar är klara</span>
                        </label>
                        <p class="form-field__hint">Mejlet skickas bara om kunden har en e-postadress. Det innehåller statuslänken och inga arbetsanteckningar.</p>
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
                                @php
                                    $coverPhoto = $sash->coverPhoto();
                                @endphp
                                @if ($coverPhoto instanceof \App\Models\SashPhoto)
                                    <img class="sash-card__thumb" src="{{ route('app.sashes.photos.show', [$project, $sash, $coverPhoto]) }}" alt="" width="64" height="64" loading="lazy" decoding="async">
                                @else
                                    <span class="sash-card__thumb sash-card__thumb--empty" aria-hidden="true">
                                        <svg viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="5" width="24" height="30" rx="1.5"/><path d="M20 5v30M8 20h24"/></svg>
                                    </span>
                                @endif
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

        @if ($projectEvents->isNotEmpty())
            <section class="sash-history" aria-labelledby="project-history-title">
                <p class="workshop-eyebrow">VAD SOM HÄNT</p>
                <h2 class="workshop-section-heading" id="project-history-title">Historik</h2>
                <ol class="sash-history__list">
                    @foreach ($projectEvents as $event)
                        <li class="sash-history__item">
                            <p class="sash-history__meta">
                                <time datetime="{{ $event->created_at?->toAtomString() }}">{{ \App\Support\SwedishDate::formatDateTime($event->created_at) }}</time>
                                <span>{{ $event->user_name }}</span>
                            </p>
                            @if (is_string($event->body) && $event->body !== '')
                                <p>{{ $event->body }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($timeTotalMinutes > 0)
            <section class="project-time" aria-labelledby="project-time-title">
                <p class="workshop-eyebrow">NEDLAGD TID</p>
                <h2 class="workshop-section-heading" id="project-time-title">Arbetstid</h2>
                <p>Totalt {{ \App\Support\WorkDuration::label($timeTotalMinutes) }}</p>
                <ul class="project-time__people">
                    @foreach ($timeByPerson as $name => $minutes)
                        <li>{{ $name }}: {{ \App\Support\WorkDuration::label($minutes) }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <p class="no-print"><a class="workshop-text-link" href="{{ route('app.projects.materials', $project) }}">Materiallista</a></p>

        @if (auth()->user()?->isOwner())
            <section class="project-danger-zone" aria-labelledby="danger-title">
                <h2 class="workshop-section-heading" id="danger-title">Ta bort projekt</h2>
                <p>Borttagning raderar projektet och bildfilerna. Det går inte att ångra. Skriv ta-bort för att bekräfta.</p>
                <form class="workshop-form" method="POST" action="{{ route('app.projects.destroy', $project) }}" data-confirm="Bågarna, historiken och bildfilerna tas bort." data-confirm-title="Ta bort projektet?" data-confirm-match="ta-bort" data-confirm-match-message="Skriv ta-bort för att bekräfta.">
                    @csrf
                    @method('DELETE')
                    <div class="form-field">
                        <label for="project-delete-confirmation">Skriv ta-bort för att bekräfta.</label>
                        <input id="project-delete-confirmation" name="confirmation" type="text" value="{{ is_string(old('confirmation')) ? old('confirmation') : '' }}" autocomplete="off" required @if ($errors->has('confirmation')) aria-invalid="true" aria-describedby="project-delete-error" @endif>
                        <p class="form-field__error" id="project-delete-error" data-confirm-error @if (! $errors->has('confirmation')) hidden @endif>@error('confirmation'){{ $message }}@enderror</p>
                    </div>
                    <button class="workshop-button workshop-button--danger" type="submit">Ta bort projekt</button>
                </form>
            </section>
        @endif

    </div>
@endsection
