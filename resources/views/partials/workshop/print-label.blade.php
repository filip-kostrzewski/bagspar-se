<article class="print-label">
    <img
        class="print-label__qr"
        src="{{ route('app.sashes.qr', [$project, $sash]) }}"
        alt="QR-kod för båge {{ $sash->code }}"
        width="220"
        height="220"
    >
    <div class="print-label__text">
        <p class="print-label__code">{{ $sash->code }}</p>
        @if ($sash->room)
            <p class="print-label__room">{{ $sash->room }}</p>
        @endif
        <p class="print-label__project">{{ $project->name }}</p>
    </div>
</article>
