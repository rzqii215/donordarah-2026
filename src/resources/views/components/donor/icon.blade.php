@props([
    'name',
])

<svg
    {{ $attributes->merge([
        'class' => 'donor-icon',
    ]) }}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    @switch($name)
        @case('home')
            <path d="M3 10.5 12 3l9 7.5" />
            <path d="M5 9.5V21h14V9.5" />
            <path d="M9 21v-7h6v7" />
            @break

        @case('calendar')
            <rect
                x="3"
                y="5"
                width="18"
                height="16"
                rx="2"
            />
            <path d="M16 3v4M8 3v4M3 10h18" />
            @break

        @case('map-pin')
            <path
                d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"
            />
            <circle
                cx="12"
                cy="10"
                r="2.5"
            />
            @break

        @case('droplet')
            <path
                d="M12 2.5S5.5 10 5.5 15a6.5 6.5 0 0 0 13 0C18.5 10 12 2.5 12 2.5Z"
            />
            @break

        @case('history')
            <path d="M3 12a9 9 0 1 0 3-6.7" />
            <path d="M3 4v5h5" />
            <path d="M12 7v5l3 2" />
            @break

        @case('user')
            <circle
                cx="12"
                cy="8"
                r="4"
            />
            <path d="M4 21a8 8 0 0 1 16 0" />
            @break

        @case('logout')
            <path d="M10 17l5-5-5-5" />
            <path d="M15 12H3" />
            <path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5" />
            @break

        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
            <path d="M10 21h4" />
            @break

        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break

        @case('close')
            <path d="m6 6 12 12M18 6 6 18" />
            @break

        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break

        @case('search')
            <circle
                cx="11"
                cy="11"
                r="7"
            />
            <path d="m20 20-4-4" />
            @break

        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break

        @case('clipboard')
            <rect
                x="5"
                y="4"
                width="14"
                height="17"
                rx="2"
            />
            <path d="M9 4.5V3h6v1.5" />
            <path d="M9 10h6M9 14h6" />
            @break

        @default
            <circle
                cx="12"
                cy="12"
                r="9"
            />
    @endswitch
</svg>
