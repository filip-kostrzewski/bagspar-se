@if (is_string($href) && $href !== '')
    <a href="{{ $href }}">{{ $phone }}</a>
@else
    {{ $phone }}
@endif
