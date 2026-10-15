@php
    $stageDefinitions = [
        'received' => 'Mottagen',
        'scraping' => 'Skrapning',
        'repair' => 'Trälagning',
        'glazing' => 'Glasning',
        'painting' => 'Målning',
        'ready' => 'Klar',
    ];
    $currentStage = array_key_exists($currentStage ?? '', $stageDefinitions) ? $currentStage : 'received';
    $currentStageIndex = array_search($currentStage, array_keys($stageDefinitions), true);
@endphp

<ol class="stage-track" aria-label="Arbetssteg, aktuellt steg: {{ $stageDefinitions[$currentStage] }}">
    @foreach ($stageDefinitions as $stageKey => $stageName)
        <li class="stage-track__step @if ($loop->index < $currentStageIndex) is-complete @elseif ($loop->index === $currentStageIndex) is-current @endif" @if ($loop->index === $currentStageIndex) aria-current="step" @endif>
            <span class="stage-track__icon">
                @include('partials.workshop.stage-icon', ['stage' => $stageKey])
            </span>
            <span class="stage-track__label">{{ $stageName }}</span>
        </li>
    @endforeach
</ol>
