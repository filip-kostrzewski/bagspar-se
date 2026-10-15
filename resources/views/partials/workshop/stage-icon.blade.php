<svg class="stage-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($stage)
        @case('received')
            <path d="M5 4.75h14a1.25 1.25 0 0 1 1.25 1.25v12A1.25 1.25 0 0 1 19 19.25H5A1.25 1.25 0 0 1 3.75 18V6A1.25 1.25 0 0 1 5 4.75Z" />
            <path d="M12 7v7m-3-3 3 3 3-3" />
            @break
        @case('scraping')
            <path d="m4.5 18.5 8.8-8.8 3 3-8.8 8.8H4.5v-3Z" />
            <path d="m11.8 11.2 2.9-2.9 4 4-2.9 2.9M4.5 21.5h15" />
            @break
        @case('repair')
            <path d="M14.1 6.1a4.5 4.5 0 0 0-5.9 5.9l-4.6 4.6a2.1 2.1 0 0 0 3 3l4.6-4.6a4.5 4.5 0 0 0 5.9-5.9l-2.8 2.8-2.6-2.6 2.8-2.8Z" />
            @break
        @case('glazing')
            <rect x="4" y="4" width="16" height="16" rx="1.5" />
            <path d="M12 4v16M4 12h16" />
            @break
        @case('painting')
            <path d="M4 5h12v5H4zM16 7.5h2.5a2 2 0 0 1 0 4H14v7a2 2 0 0 1-4 0v-4" />
            <path d="M6 5V3.75h8V5" />
            @break
        @case('ready')
            <circle cx="12" cy="12" r="8.25" />
            <path d="m8.5 12.1 2.3 2.3 4.8-5" />
            @break
        @default
            <circle cx="12" cy="12" r="8.25" />
            <path d="M12 8v4.5l3 1.75" />
    @endswitch
</svg>
