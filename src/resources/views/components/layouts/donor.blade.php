<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $title ?? 'Portal Pendonor' }}
        — {{ config('app.name', 'DonorDarah') }}
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- Dipertahankan sementara agar halaman donor lama tetap berfungsi. --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/donor-app.css') }}"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @livewireStyles

    @stack('styles')

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

@php
    $currentUser = auth()->user();

    $userName = trim(
        (string) ($currentUser?->name ?? 'Pendonor')
    );

    $nameParts = preg_split(
        '/\s+/',
        $userName
    ) ?: [];

    $userInitials = collect($nameParts)
        ->filter()
        ->take(2)
        ->map(
            fn (string $part): string =>
                mb_strtoupper(
                    mb_substr($part, 0, 1)
                )
        )
        ->implode('');

    if ($userInitials === '') {
        $userInitials = 'P';
    }

    $avatarCandidate = trim(
        (string) (
            $currentUser?->avatar_url
            ?? $currentUser?->google_avatar
            ?? ''
        )
    );

    $avatarUrl = null;

    if ($avatarCandidate !== '') {
        if (
            filter_var(
                $avatarCandidate,
                FILTER_VALIDATE_URL
            )
        ) {
            $avatarUrl = $avatarCandidate;
        } else {
            $normalizedAvatar = ltrim(
                $avatarCandidate,
                '/'
            );

            $avatarUrl = str_starts_with(
                $normalizedAvatar,
                'storage/'
            )
                ? asset($normalizedAvatar)
                : asset(
                    'storage/' . $normalizedAvatar
                );
        }
    }

    $roleNames = (
        $currentUser
        && method_exists(
            $currentUser,
            'getRoleNames'
        )
    )
        ? $currentUser->getRoleNames()
        : collect();

    $primaryRole = strtolower(
        (string) (
            $roleNames->first()
            ?? 'donor'
        )
    );

    $roleLabel = match ($primaryRole) {
        'donor',
        'pendonor' => 'Pendonor',

        'pemohon_donor' => 'Pemohon Donor',

        'super_admin',
        'super-admin' => 'Super Admin',

        'petugas' => 'Petugas',

        default => \Illuminate\Support\Str::headline(
            $primaryRole
        ),
    };

    $navigationItems = [
        [
            'route' => 'donor.beranda',
            'pattern' => 'donor.beranda',
            'icon' => 'home',
            'label' => 'Beranda',
        ],
        [
            'route' => 'donor.jadwal',
            'pattern' => 'donor.jadwal*',
            'icon' => 'calendar',
            'label' => 'Jadwal Donor',
        ],
        [
            'route' => 'donor.lokasi',
            'pattern' => 'donor.lokasi*',
            'icon' => 'map-pin',
            'label' => 'Lokasi Donor',
        ],
        [
            'route' => 'donor.stok',
            'pattern' => 'donor.stok*',
            'icon' => 'droplet',
            'label' => 'Stok Darah',
        ],
        [
            'route' => 'donor.riwayat',
            'pattern' => 'donor.riwayat*',
            'icon' => 'history',
            'label' => 'Riwayat',
        ],
        [
            'route' => 'donor.profil',
            'pattern' => 'donor.profil*',
            'icon' => 'user',
            'label' => 'Profil',
        ],
    ];

    $mobileNavigationRoutes = [
        'donor.beranda',
        'donor.jadwal',
        'donor.lokasi',
        'donor.riwayat',
        'donor.profil',
    ];
@endphp

<body
    class="min-h-full overflow-x-hidden bg-[#f9f9ff] text-[#191c20] antialiased"
    style="font-family: 'Plus Jakarta Sans', sans-serif;"
    x-data="{
        mobileMenuOpen: false,
        userMenuOpen: false
    }"
    x-on:keydown.escape.window="
        mobileMenuOpen = false;
        userMenuOpen = false;
    "
>
    <div class="min-h-screen">
        {{-- Sidebar desktop --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-[#e2e2e9] bg-white lg:flex"
        >
            <div class="px-7 pb-6 pt-8">
                <a
                    href="{{ route('donor.beranda') }}"
                    wire:navigate
                    class="inline-flex flex-col"
                >
                    <span class="text-[23px] font-bold tracking-[-0.04em] text-[#76001c]">
                        DonorDarah
                    </span>

                    <span class="mt-1 text-sm text-[#584141]">
                        Portal Pendonor
                    </span>
                </a>
            </div>

            <nav
                class="flex-1 overflow-y-auto px-4 py-4"
                aria-label="Navigasi portal pendonor"
            >
                <ul class="space-y-1">
                    @foreach ($navigationItems as $item)
                        @php
                            $isActive = request()->routeIs(
                                $item['pattern']
                            );
                        @endphp

                        <li>
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                @class([
                                    'group flex min-h-12 items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm font-semibold transition duration-150',
                                    'border-[#76001c] bg-[#ededf4] text-[#76001c]' => $isActive,
                                    'border-transparent text-[#584141] hover:bg-[#f3f3fa] hover:text-[#76001c]' => ! $isActive,
                                ])
                            >
                                <span
                                    class="flex h-5 w-5 shrink-0 items-center justify-center [&>svg]:h-5 [&>svg]:w-5"
                                >
                                    <x-donor.icon
                                        :name="$item['icon']"
                                    />
                                </span>

                                <span>
                                    {{ $item['label'] }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="border-t border-[#e2e2e9] p-4">
                <div class="mb-3 flex items-center gap-3 rounded-xl bg-[#f9f9ff] px-4 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[#191c20]">
                            {{ $userName }}
                        </p>

                        <p class="truncate text-xs text-[#8c7071]">
                            {{ $roleLabel }}
                        </p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('donor.logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="flex min-h-12 w-full items-center gap-3 rounded-xl border-l-4 border-transparent px-4 py-3 text-left text-sm font-semibold text-[#584141] transition hover:bg-[#fff1f2] hover:text-[#991b2f]"
                    >
                        <span
                            class="flex h-5 w-5 shrink-0 items-center justify-center [&>svg]:h-5 [&>svg]:w-5"
                        >
                            <x-donor.icon name="logout" />
                        </span>

                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 lg:pl-64">
            {{-- Header --}}
            <header
                class="sticky top-0 z-30 border-b border-[#e7e8ee]/80 bg-[#f9f9ff]/95 backdrop-blur"
            >
                <div class="flex h-[82px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#e2e2e9] bg-white text-[#584141] transition hover:bg-[#f3f3fa] hover:text-[#76001c] lg:hidden"
                            x-on:click="mobileMenuOpen = true"
                            aria-label="Buka menu"
                        >
                            <span class="[&>svg]:h-5 [&>svg]:w-5">
                                <x-donor.icon name="menu" />
                            </span>
                        </button>

                        <div class="min-w-0">
                            <h1 class="truncate text-2xl font-bold tracking-[-0.04em] text-[#76001c] sm:text-[34px] sm:leading-[42px]">
                                {{ $title ?? 'Portal Pendonor' }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        {{-- Belum menampilkan badge palsu karena backend notifikasi belum dibuat. --}}
                        <button
                            type="button"
                            disabled
                            title="Notifikasi belum tersedia"
                            class="hidden h-11 w-11 items-center justify-center rounded-full text-[#584141] opacity-60 sm:inline-flex"
                            aria-label="Notifikasi belum tersedia"
                        >
                            <span class="[&>svg]:h-6 [&>svg]:w-6">
                                <x-donor.icon name="bell" />
                            </span>
                        </button>

                        <div
                            class="relative"
                            x-on:click.outside="userMenuOpen = false"
                        >
                            <button
                                type="button"
                                class="flex items-center gap-3 rounded-xl p-1.5 transition hover:bg-[#ededf4]"
                                x-on:click="userMenuOpen = ! userMenuOpen"
                                x-bind:aria-expanded="userMenuOpen"
                                aria-haspopup="true"
                            >
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#e0bfbf] bg-[#ffdada] text-sm font-bold text-[#76001c]"
                                >
                                    @if ($avatarUrl)
                                        <img
                                            src="{{ $avatarUrl }}"
                                            alt="Foto {{ $userName }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        {{ $userInitials }}
                                    @endif
                                </span>

                                <span class="hidden min-w-0 text-left md:block">
                                    <strong class="block max-w-40 truncate text-sm font-semibold text-[#191c20]">
                                        {{ $userName }}
                                    </strong>

                                    <small class="block text-xs text-[#8c7071]">
                                        {{ $roleLabel }}
                                    </small>
                                </span>

                                <span
                                    class="hidden text-[#8c7071] [&>svg]:h-4 [&>svg]:w-4 md:block"
                                >
                                    <x-donor.icon name="chevron-down" />
                                </span>
                            </button>

                            <div
                                x-cloak
                                x-show="userMenuOpen"
                                x-transition.origin.top.right
                                class="absolute right-0 top-full mt-2 w-56 overflow-hidden rounded-2xl border border-[#e2e2e9] bg-white p-2 shadow-[0_16px_40px_rgba(25,28,32,0.12)]"
                            >
                                <div class="border-b border-[#ededf4] px-3 py-2.5 md:hidden">
                                    <p class="truncate text-sm font-semibold text-[#191c20]">
                                        {{ $userName }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-[#8c7071]">
                                        {{ $roleLabel }}
                                    </p>
                                </div>

                                <a
                                    href="{{ route('donor.profil') }}"
                                    wire:navigate
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-[#584141] transition hover:bg-[#f3f3fa] hover:text-[#76001c]"
                                >
                                    <span class="[&>svg]:h-5 [&>svg]:w-5">
                                        <x-donor.icon name="user" />
                                    </span>

                                    Profil Saya
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('donor.logout') }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-[#584141] transition hover:bg-[#fff1f2] hover:text-[#991b2f]"
                                    >
                                        <span class="[&>svg]:h-5 [&>svg]:w-5">
                                            <x-donor.icon name="logout" />
                                        </span>

                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Isi halaman --}}
            <main class="mx-auto w-full max-w-[1260px] px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-8">
                {{ $slot }}
            </main>
        </div>

        {{-- Navigasi bawah mobile --}}
        <nav
            class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t border-[#e2e2e9] bg-white/95 px-1 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur lg:hidden"
            aria-label="Navigasi mobile"
        >
            @foreach ($navigationItems as $item)
                @continue(
                    ! in_array(
                        $item['route'],
                        $mobileNavigationRoutes,
                        true
                    )
                )

                @php
                    $isMobileActive = request()->routeIs(
                        $item['pattern']
                    );
                @endphp

                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    @class([
                        'flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-1 py-2 text-[10px] font-semibold transition',
                        'text-[#76001c]' => $isMobileActive,
                        'text-[#8c7071]' => ! $isMobileActive,
                    ])
                >
                    <span
                        @class([
                            'flex h-8 w-10 items-center justify-center rounded-full [&>svg]:h-5 [&>svg]:w-5',
                            'bg-[#ffdada]' => $isMobileActive,
                        ])
                    >
                        <x-donor.icon
                            :name="$item['icon']"
                        />
                    </span>

                    <span class="max-w-full truncate">
                        {{ $item['label'] === 'Jadwal Donor'
                            ? 'Jadwal'
                            : (
                                $item['label'] === 'Lokasi Donor'
                                    ? 'Lokasi'
                                    : $item['label']
                            )
                        }}
                    </span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Overlay menu mobile --}}
    <div
        x-cloak
        x-show="mobileMenuOpen"
        x-transition.opacity
        x-on:click="mobileMenuOpen = false"
        class="fixed inset-0 z-40 bg-[#191c20]/45 backdrop-blur-[2px] lg:hidden"
    ></div>

    {{-- Drawer mobile --}}
    <aside
        x-cloak
        x-show="mobileMenuOpen"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 flex w-[min(86vw,320px)] flex-col border-r border-[#e2e2e9] bg-white shadow-2xl lg:hidden"
    >
        <div class="flex items-center justify-between border-b border-[#e2e2e9] px-5 py-5">
            <div>
                <p class="text-xl font-bold tracking-[-0.04em] text-[#76001c]">
                    DonorDarah
                </p>

                <p class="mt-1 text-xs text-[#8c7071]">
                    Portal Pendonor
                </p>
            </div>

            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#e2e2e9] text-[#584141]"
                x-on:click="mobileMenuOpen = false"
                aria-label="Tutup menu"
            >
                <span class="[&>svg]:h-5 [&>svg]:w-5">
                    <x-donor.icon name="close" />
                </span>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4">
            <ul class="space-y-1">
                @foreach ($navigationItems as $item)
                    @php
                        $isDrawerActive = request()->routeIs(
                            $item['pattern']
                        );
                    @endphp

                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            x-on:click="mobileMenuOpen = false"
                            @class([
                                'flex min-h-12 items-center gap-3 rounded-xl border-l-4 px-4 py-3 text-sm font-semibold transition',
                                'border-[#76001c] bg-[#ededf4] text-[#76001c]' => $isDrawerActive,
                                'border-transparent text-[#584141] hover:bg-[#f3f3fa] hover:text-[#76001c]' => ! $isDrawerActive,
                            ])
                        >
                            <span class="[&>svg]:h-5 [&>svg]:w-5">
                                <x-donor.icon
                                    :name="$item['icon']"
                                />
                            </span>

                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="border-t border-[#e2e2e9] p-4">
            <form
                method="POST"
                action="{{ route('donor.logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="flex min-h-12 w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold text-[#584141] transition hover:bg-[#fff1f2] hover:text-[#991b2f]"
                >
                    <span class="[&>svg]:h-5 [&>svg]:w-5">
                        <x-donor.icon name="logout" />
                    </span>

                    Keluar
                </button>
            </form>
        </div>
    </aside>

    @livewireScripts

    @stack('scripts')
</body>
</html>