@props([
    'title' => 'Portal Pemohon Donor',
    'heading' => 'Portal Pemohon Donor',
    'description' => null,
    'pengguna' => null,
    'profil' => null,
    'notificationCount' => 0,
])

@php
    $penggunaAktif = $pengguna ?? auth()->user();

    $namaPemohon =
        $profil?->nama_rumah_sakit
        ?? $penggunaAktif?->name
        ?? 'Pemohon Donor';

    $inisial = collect(
        explode(' ', $namaPemohon)
    )
        ->filter()
        ->take(2)
        ->map(
            fn (string $bagian): string =>
                mb_substr($bagian, 0, 1)
        )
        ->implode('');

    $inisial = filled($inisial)
        ? mb_strtoupper($inisial)
        : 'PD';

    $portalUrl = function (
        string $routeName
    ): string {
        return \Illuminate\Support\Facades\Route::has(
            $routeName
        )
            ? route($routeName)
            : route('pemohon-donor.beranda');
    };

    $menuUtama = [
        [
            'label' => 'Dashboard',
            'route' => 'pemohon-donor.beranda',
            'active' => 'pemohon-donor.beranda',
            'icon' => 'home',
        ],
        [
            'label' => 'Pengajuan',
            'route' => 'pemohon-donor.pengajuan.index',
            'active' => 'pemohon-donor.pengajuan.*',
            'icon' => 'file',
        ],
        [
            'label' => 'Distribusi',
            'route' => 'pemohon-donor.distribusi.index',
            'active' => 'pemohon-donor.distribusi.*',
            'icon' => 'truck',
        ],
        [
            'label' => 'Profil',
            'route' => 'pemohon-donor.profil.index',
            'active' => 'pemohon-donor.profil.*',
            'icon' => 'user',
        ],
        [
            'label' => 'Riwayat',
            'route' => 'pemohon-donor.riwayat.index',
            'active' => 'pemohon-donor.riwayat.*',
            'icon' => 'history',
        ],
    ];

    $menuTambahan = [
        [
            'label' => 'Bantuan',
            'route' => 'pemohon-donor.bantuan.index',
            'active' => 'pemohon-donor.bantuan.*',
            'icon' => 'help',
        ],
        [
            'label' => 'Pengaturan',
            'route' => 'pemohon-donor.pengaturan.index',
            'active' => 'pemohon-donor.pengaturan.*',
            'icon' => 'settings',
        ],
    ];

    $icon = fn (string $nama): string => match ($nama) {
        'drop' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 2.5S5.5 10 5.5 15a6.5 6.5 0 0 0 13 0C18.5 10 12 2.5 12 2.5Z"/>
                <path d="m9.5 14 1.7 1.7 3.6-4"/>
            </svg>
        ',

        'home' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="m3 11 9-8 9 8"/>
                <path d="M5 10v10h14V10"/>
                <path d="M9 20v-6h6v6"/>
            </svg>
        ',

        'file' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                <path d="M14 2v6h6"/>
                <path d="M8 13h8M8 17h5"/>
            </svg>
        ',

        'truck' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M3 6h11v10H3z"/>
                <path d="M14 10h4l3 3v3h-7z"/>
                <circle cx="7" cy="18" r="2"/>
                <circle cx="17" cy="18" r="2"/>
            </svg>
        ',

        'user' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 21a8 8 0 0 1 16 0"/>
            </svg>
        ',

        'history' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                <path d="M3 3v6h6"/>
                <path d="M12 7v5l3 2"/>
            </svg>
        ',

        'help' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="9"/>
                <path d="M9.5 9a2.7 2.7 0 1 1 5 1.4c-.8 1.2-2.5 1.4-2.5 3.6"/>
                <path d="M12 18h.01"/>
            </svg>
        ',

        'settings' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21h-4v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3v-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.3 7 7.1 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V3h4v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.1v4H21a1.7 1.7 0 0 0-1.6 1Z"/>
            </svg>
        ',

        'logout' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M10 17l5-5-5-5"/>
                <path d="M15 12H3"/>
                <path d="M21 3v18"/>
            </svg>
        ',

        'search' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="7"/>
                <path d="m20 20-3.5-3.5"/>
            </svg>
        ',

        'bell' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                <path d="M10 21h4"/>
            </svg>
        ',

        default => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="9"/>
            </svg>
        ',
    };
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $title }} — {{ config('app.name', 'Donor Darah') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @livewireStyles
    @stack('styles')
</head>

<body
    class="min-h-screen bg-[#f8f7f6] text-[#191c20] antialiased"
>
    <div
        class="min-h-screen lg:grid lg:grid-cols-[280px_minmax(0,1fr)]"
    >
        <aside
            class="hidden border-r border-[#e9e4e1] bg-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col"
        >
            <div class="px-6 pb-4 pt-7">
                <a
                    href="{{ $portalUrl('pemohon-donor.beranda') }}"
                    class="flex items-center gap-3"
                >
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#991b2f] text-white shadow-[0_14px_30px_rgba(153,27,47,0.22)]"
                    >
                        <span class="h-6 w-6">
                            {!! $icon('drop') !!}
                        </span>
                    </span>

                    <span>
                        <span
                            class="block text-xs font-bold uppercase tracking-[0.14em] text-[#8c7071]"
                        >
                            Portal
                        </span>

                        <strong
                            class="block text-lg tracking-[-0.04em] text-[#76001c]"
                        >
                            Pemohon Donor
                        </strong>
                    </span>
                </a>
            </div>

            <nav
                class="flex-1 overflow-y-auto px-4 py-5"
            >
                <p
                    class="px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#a48b8c]"
                >
                    Menu Utama
                </p>

                <div class="mt-3 space-y-1.5">
                    @foreach ($menuUtama as $menu)
                        @php
                            $aktif = request()
                                ->routeIs(
                                    $menu['active']
                                );
                        @endphp

                        <a
                            href="{{ $portalUrl($menu['route']) }}"
                            @class([
                                'flex min-h-12 items-center gap-3 rounded-xl px-3.5 text-sm font-semibold transition',
                                'bg-[#fff0f3] text-[#991b2f]' => $aktif,
                                'text-[#655253] hover:bg-[#faf4f5] hover:text-[#991b2f]' => ! $aktif,
                            ])
                            @if ($aktif)
                                aria-current="page"
                            @endif
                        >
                            <span class="h-5 w-5">
                                {!! $icon($menu['icon']) !!}
                            </span>

                            {{ $menu['label'] }}
                        </a>
                    @endforeach
                </div>

                <div
                    class="my-6 border-t border-[#eee9e6]"
                ></div>

                <p
                    class="px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-[#a48b8c]"
                >
                    Lainnya
                </p>

                <div class="mt-3 space-y-1.5">
                    @foreach ($menuTambahan as $menu)
                        @php
                            $aktif = request()
                                ->routeIs(
                                    $menu['active']
                                );
                        @endphp

                        <a
                            href="{{ $portalUrl($menu['route']) }}"
                            @class([
                                'flex min-h-12 items-center gap-3 rounded-xl px-3.5 text-sm font-semibold transition',
                                'bg-[#fff0f3] text-[#991b2f]' => $aktif,
                                'text-[#655253] hover:bg-[#faf4f5] hover:text-[#991b2f]' => ! $aktif,
                            ])
                        >
                            <span class="h-5 w-5">
                                {!! $icon($menu['icon']) !!}
                            </span>

                            {{ $menu['label'] }}
                        </a>
                    @endforeach

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="flex min-h-12 w-full items-center gap-3 rounded-xl px-3.5 text-left text-sm font-semibold text-[#655253] transition hover:bg-[#fff0f3] hover:text-[#991b2f]"
                        >
                            <span class="h-5 w-5">
                                {!! $icon('logout') !!}
                            </span>

                            Keluar
                        </button>
                    </form>
                </div>
            </nav>

            <div class="p-5">
                <div
                    class="rounded-[22px] border border-[#f0d7dc] bg-[#fff5f7] p-5 text-center"
                >
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl text-[#991b2f] shadow-sm"
                    >
                        ♥
                    </div>

                    <p
                        class="mt-3 text-sm font-bold leading-6 text-[#76001c]"
                    >
                        Setetes darah,<br>
                        sejuta harapan.
                    </p>
                </div>
            </div>
        </aside>

        <div class="min-w-0">
            <header
                class="sticky top-0 z-40 border-b border-[#e9e4e1] bg-white/95 backdrop-blur-xl"
            >
                <div
                    class="flex min-h-[88px] items-center gap-4 px-4 sm:px-6 lg:px-8"
                >
                    <details class="relative lg:hidden">
                        <summary
                            class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-[#e6dfdc] bg-white text-[#76001c]"
                            aria-label="Buka navigasi"
                        >
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </summary>

                        <div
                            class="absolute left-0 top-14 z-50 w-[min(86vw,320px)] rounded-2xl border border-[#e8e1de] bg-white p-3 shadow-[0_24px_70px_rgba(25,28,32,0.18)]"
                        >
                            <div
                                class="border-b border-[#eee9e6] px-3 pb-3"
                            >
                                <strong
                                    class="text-sm text-[#76001c]"
                                >
                                    Portal Pemohon Donor
                                </strong>
                            </div>

                            <nav class="mt-2 space-y-1">
                                @foreach (
                                    array_merge(
                                        $menuUtama,
                                        $menuTambahan
                                    ) as $menu
                                )
                                    <a
                                        href="{{ $portalUrl($menu['route']) }}"
                                        class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-[#655253] hover:bg-[#fff0f3] hover:text-[#991b2f]"
                                    >
                                        <span class="h-5 w-5">
                                            {!! $icon($menu['icon']) !!}
                                        </span>

                                        {{ $menu['label'] }}
                                    </a>
                                @endforeach

                                <form
                                    method="POST"
                                    action="{{ route('logout') }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-sm font-semibold text-[#655253] hover:bg-[#fff0f3] hover:text-[#991b2f]"
                                    >
                                        <span class="h-5 w-5">
                                            {!! $icon('logout') !!}
                                        </span>

                                        Keluar
                                    </button>
                                </form>
                            </nav>
                        </div>
                    </details>

                    <div class="min-w-0 flex-1">
                        <h1
                            class="truncate text-xl font-bold tracking-[-0.04em] text-[#191c20] sm:text-2xl"
                        >
                            {{ $heading }}
                        </h1>

                        @if (filled($description))
                            <p
                                class="mt-1 hidden truncate text-sm text-[#755f60] sm:block"
                            >
                                {{ $description }}
                            </p>
                        @endif
                    </div>

                    <form
                        method="GET"
                        action="{{ $portalUrl('pemohon-donor.pengajuan.index') }}"
                        class="relative hidden w-full max-w-sm md:block"
                    >
                        <svg
                            class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#967d7e]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>

                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari pengajuan..."
                            class="min-h-12 w-full rounded-xl border border-[#e4ddda] bg-[#faf8f7] pl-12 pr-4 text-sm outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                        >
                    </form>

                    <div
                        class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#e6dfdc] bg-white text-[#655253]"
                    >
                        <span class="h-5 w-5">
                            {!! $icon('bell') !!}
                        </span>

                        @if ((int) $notificationCount > 0)
                            <span
                                class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#991b2f] px-1 text-[10px] font-bold text-white"
                            >
                                {{ (int) $notificationCount > 9 ? '9+' : $notificationCount }}
                            </span>
                        @endif
                    </div>

                    <a
                        href="{{ $portalUrl('pemohon-donor.profil.index') }}"
                        class="flex min-w-0 items-center gap-3"
                    >
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#ffe3e9] text-sm font-bold text-[#991b2f]"
                        >
                            {{ $inisial }}
                        </span>

                        <span
                            class="hidden min-w-0 xl:block"
                        >
                            <strong
                                class="block max-w-44 truncate text-sm text-[#191c20]"
                            >
                                {{ $namaPemohon }}
                            </strong>

                            <span
                                class="text-xs text-[#8c7071]"
                            >
                                Pemohon Donor
                            </span>
                        </span>
                    </a>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div
                        class="mb-5 rounded-2xl border border-[#cde8d6] bg-[#effbf3] px-5 py-4 text-sm font-semibold text-[#176b3a]"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="mb-5 rounded-2xl border border-[#f0cbd2] bg-[#fff3f5] px-5 py-4 text-sm font-semibold text-[#991b2f]"
                    >
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-5 rounded-2xl border border-[#f0cbd2] bg-[#fff3f5] px-5 py-4"
                    >
                        <strong
                            class="text-sm text-[#991b2f]"
                        >
                            Periksa kembali data yang dimasukkan.
                        </strong>

                        <ul
                            class="mt-2 list-disc space-y-1 pl-5 text-sm text-[#75525a]"
                        >
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>