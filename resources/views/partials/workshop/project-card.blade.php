@php
    $projectIsComplete = $project->status === 'completed';
@endphp
<article class="project-card">
    <a class="project-card__link" href="{{ route('app.projects.show', $project) }}">
        <div class="project-card__topline">
            <span class="project-card__status @if ($project->isArchived()) project-card__status--archived @elseif ($projectIsComplete) project-card__status--complete @endif">
                <span class="status-dot" aria-hidden="true"></span>
                {{ $project->isArchived() ? 'Arkiverad' : ($projectIsComplete ? 'Avslutat' : 'Pågår') }}
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
        @if ($project->delivery_date)
            <p class="project-card__location">Leveransdatum {{ $project->delivery_date->toDateString() }}</p>
        @endif
        <div class="project-card__footer">
            <span>Öppna arbetsöversikten</span>
            <span>Visa projekt <span aria-hidden="true">→</span></span>
        </div>
    </a>
</article>
