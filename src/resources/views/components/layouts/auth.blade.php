<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $judulHalaman = match (true) {
            request()->routeIs('login') => 'Masuk',
            request()->routeIs('password.request') => 'Lupa Password',
            request()->routeIs('password.reset') => 'Atur Ulang Password',
            request()->is('register/donor') => 'Daftar Pendonor',
            request()->is('register/pemohon-donor') => 'Daftar Pemohon Donor',
            default => 'Akun',
        };

        $kontenVisual = match (true) {
            request()->is('register/donor') => [
                'kicker' => 'Bergabung sebagai pendonor',
                'judul' => 'Satu pendaftaran, banyak kesempatan untuk menolong.',
                'deskripsi' => 'Lengkapi profil kesehatan dasar Anda dan temukan jadwal donor yang sesuai dengan lokasi serta waktu Anda.',
            ],
            request()->is('register/pemohon-donor') => [
                'kicker' => 'Untuk rumah sakit dan institusi',
                'judul' => 'Ajukan kebutuhan darah dengan proses yang lebih jelas.',
                'deskripsi' => 'Pantau status pengajuan dan distribusi darah melalui satu portal yang aman dan terintegrasi.',
            ],
            request()->routeIs('password.request') || request()->routeIs('password.reset') => [
                'kicker' => 'Pemulihan akun aman',
                'judul' => 'Kembali mengakses layanan donor darah Anda.',
                'deskripsi' => 'Kami menggunakan tautan terbatas waktu untuk membantu Anda mengatur ulang password dengan aman.',
            ],
            default => [
                'kicker' => 'Pelayanan donor terintegrasi',
                'judul' => 'Setetes darah Anda memberi harapan yang nyata.',
                'deskripsi' => 'Masuk untuk menemukan jadwal donor, memantau riwayat, atau mengelola pengajuan kebutuhan darah.',
            ],
        };

        $pengaturanTampilan = \Illuminate\Support\Facades\Cache::remember(
            'pengaturan-tampilan.auth',
            now()->addHour(),
            static function (): array {
                if (! \Illuminate\Support\Facades\Schema::hasTable('pengaturan_tampilan')) {
                    return [];
                }

                return \App\Models\PengaturanTampilan::query()
                    ->first()
                    ?->only([
                        'gambar_auth',
                        'gambar_auth_alt',
                    ]) ?? [];
            }
        );

        $pathGambarAuth = trim(
            (string) ($pengaturanTampilan['gambar_auth'] ?? '')
        );

        $gambarAuthUrl = $pathGambarAuth !== ''
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($pathGambarAuth)
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($pathGambarAuth)
                : null;

        $gambarAuthAlt = filled($pengaturanTampilan['gambar_auth_alt'] ?? null)
            ? (string) $pengaturanTampilan['gambar_auth_alt']
            : 'Petugas kesehatan mendampingi pendonor darah di pusat donor yang nyaman';
    @endphp

    <title>{{ $judulHalaman }} — {{ config('app.name', 'Donor Darah') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="/css/auth-app.css?v={{ filemtime(public_path('css/auth-app.css')) }}"
    >

    @livewireStyles
</head>

<body>
    <div class="auth-shell">
        <aside class="auth-story" aria-label="Informasi Donor Darah">
            @if ($gambarAuthUrl !== null)
                <img
                    class="auth-story-image"
                    src="{{ $gambarAuthUrl }}"
                    alt="{{ $gambarAuthAlt }}"
                >
            @else
                <div
                    class="auth-story-placeholder"
                    aria-hidden="true"
                ></div>
            @endif

            <div class="auth-story-overlay"></div>

            <div class="auth-story-inner">
                <a href="{{ url('/') }}" class="auth-brand" aria-label="Kembali ke halaman utama">
                    <span class="auth-brand-mark">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M12 3S5.5 9.6 5.5 14.4a6.5 6.5 0 0 0 13 0C18.5 9.6 12 3 12 3Z"/>
                            <path d="M9 15.2a3.1 3.1 0 0 0 3 2.4"/>
                        </svg>
                    </span>

                    <span class="auth-brand-copy">
                        <strong>{{ config('app.name', 'Donor Darah') }}</strong>
                        <span>Terhubung untuk menolong</span>
                    </span>
                </a>

                <div class="auth-story-content">
                    <p class="auth-eyebrow">{{ $kontenVisual['kicker'] }}</p>
                    <h1>{{ $kontenVisual['judul'] }}</h1>
                    <p>{{ $kontenVisual['deskripsi'] }}</p>

                    <div class="auth-trust-list" aria-label="Keunggulan layanan">
                        <span class="auth-trust-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            Data terlindungi
                        </span>

                        <span class="auth-trust-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M8 2v4M16 2v4"/>
                                <rect x="3" y="4" width="18" height="18" rx="3"/>
                                <path d="M3 10h18"/>
                            </svg>
                            Jadwal real-time
                        </span>

                        <span class="auth-trust-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/>
                                <circle cx="12" cy="10" r="2"/>
                            </svg>
                            Lokasi terverifikasi
                        </span>
                    </div>
                </div>

                <div class="auth-story-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 8v4l2.5 2.5"/>
                    </svg>
                    Layanan dapat diakses 24 jam untuk informasi dan pengajuan.
                </div>
            </div>
        </aside>

        <main class="auth-main {{ request()->is('register/*') ? 'auth-main--top' : '' }}">
            <div class="auth-card {{ request()->is('register/*') ? 'auth-card--wide' : '' }}">
                <a href="{{ url('/') }}" class="auth-brand auth-mobile-brand">
                    <span class="auth-brand-mark">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M12 3S5.5 9.6 5.5 14.4a6.5 6.5 0 0 0 13 0C18.5 9.6 12 3 12 3Z"/>
                            <path d="M9 15.2a3.1 3.1 0 0 0 3 2.4"/>
                        </svg>
                    </span>

                    <span class="auth-brand-copy">
                        <strong>{{ config('app.name', 'Donor Darah') }}</strong>
                        <span>Terhubung untuk menolong</span>
                    </span>
                </a>

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    @includeWhen(
        view()->exists('components.shared.safe-flash-message'),
        'components.shared.safe-flash-message'
    )
</body>
</html>