@switch($name)
    @case('arrow-right')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4.75 12h14.5M13 5.75 19.25 12 13 18.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break
    @case('tag')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12.4 3.5H5.5v6.9l9.1 9.1a1.4 1.4 0 0 0 2 0l3-3a1.4 1.4 0 0 0 0-2l-7.2-7.2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
            <circle cx="8.4" cy="7.5" r="1" fill="currentColor" />
        </svg>
        @break
    @case('image')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="4.5" width="17" height="15" rx="2" stroke="currentColor" stroke-width="1.7" />
            <circle cx="9" cy="9" r="1.6" stroke="currentColor" stroke-width="1.6" />
            <path d="m4.5 16 4.3-4.1a1 1 0 0 1 1.4 0l2.3 2.2 2-1.9a1 1 0 0 1 1.4 0l3.6 3.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
        </svg>
        @break
    @case('check')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m5.5 12.5 4.2 4.2 8.8-9.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break
    @case('account')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-width="1.7" />
            <path d="M5.25 20c.3-3.7 2.65-5.7 6.75-5.7s6.45 2 6.75 5.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
        </svg>
        @break
    @case('project')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 7.25A2.25 2.25 0 0 1 6.25 5h4l1.8 2h5.7A2.25 2.25 0 0 1 20 9.25v8.5A2.25 2.25 0 0 1 17.75 20h-11.5A2.25 2.25 0 0 1 4 17.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
            <path d="M4.5 10h15" stroke="currentColor" stroke-width="1.7" />
        </svg>
        @break
    @case('lock')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="5" y="10" width="14" height="10" rx="1.75" stroke="currentColor" stroke-width="1.7" />
            <path d="M8 10V7.5a4 4 0 0 1 8 0V10m-4 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
        </svg>
        @break
    @case('print')
        <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7 8V4.5h10V8M7 17H5.5A1.5 1.5 0 0 1 4 15.5v-5A1.5 1.5 0 0 1 5.5 9h13a1.5 1.5 0 0 1 1.5 1.5v5a1.5 1.5 0 0 1-1.5 1.5H17" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
            <path d="M7 14h10v6H7zm10-2.5h.01" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
        </svg>
        @break
@endswitch
