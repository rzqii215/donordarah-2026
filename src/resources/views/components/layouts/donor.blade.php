<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
        — {{ config('app.name', 'donordarah') }}
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
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/donor-app.css') }}"
    >

    @livewireStyles
</head>

<body
    class="donor-body"
    x-data="{
        mobileMenuOpen: false,
        userMenuOpen: false
    }"
    x-on:keydown.escape.window="
        mobileMenuOpen = false;
        userMenuOpen = false;
    "
>
    <div class="donor-app-shell">
        <aside class="donor-sidebar donor-sidebar-desktop">
            <div class="donor-brand">
                <div class="donor-brand-mark">
                    <svg
                        viewBox="0 0 48 48"
                        aria-hidden="true"
                    >
                        <path
                            d="M24 4C19 12 10 21 10 30a14 14 0 1 0 28 0C38 21 29 12 24 4Z"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="4"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M18 30c0 4 3 7 7 7"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>

                <div class="donor-brand-copy">
                    <span>DONOR</span>
                    <strong>DARAH</strong>
                </div>
            </div>

            <nav class="donor-sidebar-nav">
                <x-donor.nav-item
                    route="donor.beranda"
                    icon="home"
                    label="Beranda"
                />

                <x-donor.nav-item
                    route="donor.jadwal"
                    icon="calendar"
                    label="Jadwal Donor"
                />

                <x-donor.nav-item
                    route="donor.lokasi"
                    icon="map-pin"
                    label="Lokasi Donor"
                />

                <x-donor.nav-item
                    route="donor.stok"
                    icon="droplet"
                    label="Stok Darah"
                />

                <x-donor.nav-item
                    route="donor.riwayat"
                    icon="history"
                    label="Riwayat"
                />

                <x-donor.nav-item
                    route="donor.profil"
                    icon="user"
                    label="Profil"
                />
            </nav>

            <form
                method="POST"
                action="{{ route('donor.logout') }}"
                class="donor-sidebar-logout"
            >
                @csrf

                <button
                    type="submit"
                    class="donor-nav-item donor-logout-button"
                >
                    <x-donor.icon name="logout" />

                    <span>Keluar</span>
                </button>
            </form>
        </aside>

        <header class="donor-topbar">
            <div class="donor-mobile-brand">
                <button
                    type="button"
                    class="donor-icon-button"
                    x-on:click="mobileMenuOpen = true"
                    aria-label="Buka menu"
                >
                    <x-donor.icon name="menu" />
                </button>

                <div class="donor-mobile-brand-text">
                    DONOR
                    <strong>DARAH</strong>
                </div>
            </div>

            <div class="donor-topbar-title">
                {{ $title ?? 'Portal Pendonor' }}
            </div>

            <div class="donor-topbar-actions">
                <button
                    type="button"
                    class="donor-notification-button"
                    aria-label="Notifikasi"
                >
                    <x-donor.icon name="bell" />

                    <span class="donor-notification-dot"></span>
                </button>

                <div
                    class="donor-user-menu"
                    x-on:click.outside="userMenuOpen = false"
                >
                    <button
                        type="button"
                        class="donor-user-trigger"
                        x-on:click="userMenuOpen = ! userMenuOpen"
                    >
                        <span class="donor-user-avatar">
                            {{ strtoupper(
                                mb_substr(
                                    auth()->user()->name ?? 'P',
                                    0,
                                    1
                                )
                            ) }}
                        </span>

                        <span class="donor-user-copy">
                            <strong>
                                {{ auth()->user()->name ?? 'Pendonor' }}
                            </strong>

                            <small>Pendonor</small>
                        </span>

                        <x-donor.icon name="chevron-down" />
                    </button>

                    <div
                        class="donor-user-dropdown"
                        x-cloak
                        x-show="userMenuOpen"
                        x-transition.origin.top.right
                    >
                        <a
                            href="{{ route('donor.profil') }}"
                            wire:navigate
                        >
                            <x-donor.icon name="user" />
                            Profil Saya
                        </a>

                        <form
                            method="POST"
                            action="{{ route('donor.logout') }}"
                        >
                            @csrf

                            <button type="submit">
                                <x-donor.icon name="logout" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="donor-main-content">
            {{ $slot }}
        </main>

        <nav class="donor-mobile-bottom-nav">
            <x-donor.nav-item
                route="donor.beranda"
                icon="home"
                label="Beranda"
                mobile
            />

            <x-donor.nav-item
                route="donor.jadwal"
                icon="calendar"
                label="Jadwal"
                mobile
            />

            <x-donor.nav-item
                route="donor.stok"
                icon="droplet"
                label="Stok"
                mobile
            />

            <x-donor.nav-item
                route="donor.profil"
                icon="user"
                label="Profil"
                mobile
            />
        </nav>
    </div>

    <div
        class="donor-mobile-overlay"
        x-cloak
        x-show="mobileMenuOpen"
        x-transition.opacity
        x-on:click="mobileMenuOpen = false"
    ></div>

    <aside
        class="donor-mobile-drawer"
        x-cloak
        x-show="mobileMenuOpen"
        x-transition:enter="donor-drawer-enter"
        x-transition:enter-start="donor-drawer-enter-start"
        x-transition:enter-end="donor-drawer-enter-end"
        x-transition:leave="donor-drawer-leave"
        x-transition:leave-start="donor-drawer-leave-start"
        x-transition:leave-end="donor-drawer-leave-end"
    >
        <div class="donor-mobile-drawer-header">
            <div class="donor-brand">
                <div class="donor-brand-mark">
                    <x-donor.icon name="droplet" />
                </div>

                <div class="donor-brand-copy">
                    <span>DONOR</span>
                    <strong>DARAH</strong>
                </div>
            </div>

            <button
                type="button"
                class="donor-icon-button"
                x-on:click="mobileMenuOpen = false"
            >
                <x-donor.icon name="close" />
            </button>
        </div>

        <nav class="donor-sidebar-nav">
            <x-donor.nav-item
                route="donor.beranda"
                icon="home"
                label="Beranda"
            />

            <x-donor.nav-item
                route="donor.jadwal"
                icon="calendar"
                label="Jadwal Donor"
            />

            <x-donor.nav-item
                route="donor.lokasi"
                icon="map-pin"
                label="Lokasi Donor"
            />

            <x-donor.nav-item
                route="donor.stok"
                icon="droplet"
                label="Stok Darah"
            />

            <x-donor.nav-item
                route="donor.riwayat"
                icon="history"
                label="Riwayat"
            />

            <x-donor.nav-item
                route="donor.profil"
                icon="user"
                label="Profil"
            />
        </nav>
    </aside>

    @livewireScripts
</body>
</html>