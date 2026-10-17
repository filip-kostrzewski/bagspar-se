@extends('layouts.workshop')

@section('title', 'Verkstadens projekt — Bågspår')

@section('content')
    <div class="workspace-page dashboard-page">
        <div class="workspace-heading-row">
            <div>
                <p class="workshop-eyebrow">{{ $workshop->name }}</p>
                <h1 class="workshop-heading" id="page-title">Projekt</h1>
                <p class="workspace-lede">Håll ihop varje projekt, båge och arbetssteg.</p>
            </div>
            <div class="workspace-heading-row__actions">
                @if (auth()->user()?->isOwner())
                    <a class="workshop-button workshop-button--quiet" href="{{ route('app.export') }}">Ladda ner CSV</a>
                @endif
                @if ($projects->isNotEmpty() || $hasAnyProjects)
                    <a class="workshop-button workshop-button--primary" href="{{ route('app.projects.create') }}">
                        <span aria-hidden="true">＋</span> Nytt projekt
                    </a>
                @endif
            </div>
        </div>

        @if (! $hasAnyProjects)
            <section class="dashboard-empty" aria-labelledby="empty-title">
                <div class="dashboard-empty__intro">
                    <span class="dashboard-empty__mark" aria-hidden="true">
                        <svg viewBox="0 0 72 72" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="13" y="10" width="46" height="52" rx="2" />
                            <path d="M36 10v52M13 36h46M22 19h7m14 0h7M22 45h7m14 0h7" />
                            <path d="M23 55h26" />
                        </svg>
                    </span>
                    <p class="workshop-eyebrow">EN LUGN START</p>
                    <h2 id="empty-title">Ge första projektet en plats.</h2>
                    <p>Samla kund, adress och alla bågar i samma arbetsflöde. Du kan lägga till detaljer efter hand.</p>
                </div>

                <ol class="dashboard-empty__steps">
                    <li>
                        <span class="dashboard-empty__step-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5 12 3l8 3.5-8 3.5L4 6.5Z"/><path d="M4 12l8 3.5 8-3.5M4 17.5 12 21l8-3.5"/></svg>
                        </span>
                        <span><strong>Skapa ett projekt</strong><small>Namnge jobbet och lägg till kund eller plats.</small></span>
                    </li>
                    <li>
                        <span class="dashboard-empty__step-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3.5" width="14" height="17" rx="1.5"/><path d="M9 8h6M9 12h6M9 16h3"/></svg>
                        </span>
                        <span><strong>Ge varje båge en kod</strong><small>Knyt rum, plats och anteckningar till rätt båge.</small></span>
                    </li>
                    <li>
                        <span class="dashboard-empty__step-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5 9.2 17 20 6.5"/><path d="M20 12a8 8 0 1 1-4-6.93"/></svg>
                        </span>
                        <span><strong>Följ arbetet</strong><small>Uppdatera steg och samla bilder på arbetskortet.</small></span>
                    </li>
                </ol>

                <a class="workshop-button workshop-button--primary dashboard-empty__action" href="{{ route('app.projects.create') }}">Skapa första projektet</a>
            </section>
        @else
            <section class="workshop-stat-grid" aria-label="Översikt">
                <article class="workshop-stat">
                    <span class="workshop-stat__label">Pågående projekt</span>
                    <strong class="workshop-stat__value">{{ $activeProjectsCount }}</strong>
                </article>
                <article class="workshop-stat">
                    <span class="workshop-stat__label">Bågar i arbete</span>
                    <strong class="workshop-stat__value">{{ $sashesInProgressCount }}</strong>
                </article>
                <article class="workshop-stat">
                    <span class="workshop-stat__label">Avslutade projekt</span>
                    <strong class="workshop-stat__value">{{ $completedProjectsCount }}</strong>
                </article>
            </section>

            <form class="project-filters" method="GET" action="{{ route('app.dashboard') }}">
                <div class="form-field">
                    <label for="filter-customer">Kund</label>
                    <input id="filter-customer" name="kund" type="search" value="{{ $filters['kund'] }}" maxlength="160">
                </div>
                <div class="form-field">
                    <label for="filter-room">Rum</label>
                    <input id="filter-room" name="rum" type="search" value="{{ $filters['rum'] }}" maxlength="120">
                </div>
                <div class="form-field">
                    <label for="filter-stage">Etapp</label>
                    <select id="filter-stage" name="etapp">
                        <option value="">Alla etapper</option>
                        @foreach (['received' => 'Mottagen', 'scraping' => 'Skrapning', 'repair' => 'Trälagning', 'glazing' => 'Glasning', 'painting' => 'Målning', 'ready' => 'Klar'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['etapp'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="workshop-checkbox" for="filter-archived">
                    <input id="filter-archived" name="arkiv" type="checkbox" value="1" @checked($showingArchived)>
                    <span>Visa arkiverade</span>
                </label>
                <button class="workshop-button workshop-button--secondary" type="submit">Filtrera</button>
                @if ($hasActiveFilters || $showingArchived)
                    <a class="workshop-text-link" href="{{ route('app.dashboard') }}">Rensa</a>
                @endif
            </form>

            <section class="project-list-section" aria-labelledby="projects-title">
                <div class="section-heading-row">
                    <div>
                        <p class="workshop-eyebrow">VERKSTADENS ARBETE</p>
                        <h2 class="workshop-section-heading" id="projects-title">{{ $showingArchived ? 'Arkiverade projekt' : 'Alla projekt' }} <span>{{ $projects->count() }}</span></h2>
                    </div>
                    @if ($archivedCount > 0 || $showingArchived)
                        <a class="workshop-text-link" href="{{ $showingArchived ? route('app.dashboard') : route('app.dashboard', ['arkiv' => 1]) }}">{{ $showingArchived ? 'Visa aktuella projekt' : 'Visa arkiverade ('.$archivedCount.')' }}</a>
                    @endif
                </div>

                @if ($projects->isEmpty())
                    <p>{{ $hasActiveFilters ? 'Inga projekt matchar sökningen.' : ($showingArchived ? 'Inga arkiverade projekt.' : 'Inga aktuella projekt.') }}</p>
                @endif

                <div class="project-grid">
                    @foreach ($projects as $project)
                        @php
                            $projectIsComplete = $project->status === 'completed';
                        @endphp
                        <article class="project-card">
                            <a class="project-card__link" href="{{ route('app.projects.show', $project) }}">
                                <div class="project-card__topline">
                                    <span class="project-card__status @if ($projectIsComplete) project-card__status--complete @endif">
                                        <span class="status-dot" aria-hidden="true"></span>
                                        {{ $projectIsComplete ? 'Avslutat' : 'Pågår' }}
                                    </span>
                                    <span class="project-card__arrow" aria-hidden="true">↗</span>
                                </div>
                                <h3>{{ $project->name }}</h3>
                                @if ($project->customerLabel())
                                    <p class="project-card__customer">{{ $project->customerLabel() }}</p>
                                @endif
                                @if ($project->location)
                                    <p class="project-card__location">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 8.2c0 4-6 9-6 9s-6-5-6-9a6 6 0 1 1 12 0Z"/><circle cx="10" cy="8" r="2"/></svg>
                                        {{ $project->location }}
                                    </p>
                                @endif
                                <div class="project-card__footer">
                                    <span>Öppna arbetsöversikten</span>
                                    <span>Visa projekt <span aria-hidden="true">→</span></span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
