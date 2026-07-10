<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Riwayat Aktivitas</title>

    <style>
        :root {
            --red: #ef1d26;
            --red-soft: #fff1f2;
            --red-border: #fecdd3;
            --blue: #2563eb;
            --blue-soft: #dbeafe;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --yellow: #d97706;
            --yellow-soft: #fef3c7;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e5e7eb;
            --soft-line: #f1f5f9;
            --surface: #ffffff;
            --body: #f8fafc;
            --shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
            --shadow-soft: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(
                    circle at top right,
                    rgba(254, 226, 226, 0.72),
                    transparent 28rem
                ),
                linear-gradient(
                    180deg,
                    #ffffff 0%,
                    var(--body) 45%,
                    #ffffff 100%
                );
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select {
            font: inherit;
        }

        svg {
            display: block;
            width: 1.25rem;
            height: 1.25rem;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        .app {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 26px 20px 20px;
            border-right: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 58px;
            margin-bottom: 34px;
        }

        .brand-mark {
            display: grid;
            width: 52px;
            height: 52px;
            place-items: center;
            color: #ffffff;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--red), #f43f5e);
            box-shadow: 0 18px 35px rgba(239, 29, 38, 0.22);
        }

        .brand-title {
            display: grid;
            gap: 1px;
            color: var(--text);
            font-size: 16px;
            font-weight: 800;
            line-height: 1.05;
            text-transform: uppercase;
        }

        .brand-title strong {
            color: var(--red);
            font-size: 19px;
        }

        .menu {
            display: grid;
            gap: 8px;
        }

        .menu-link,
        .menu-button {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            min-height: 52px;
            padding: 0 16px;
            color: #475569;
            border: 0;
            border-radius: 14px;
            background: transparent;
            font-size: 15px;
            font-weight: 750;
            text-align: left;
            cursor: pointer;
        }

        .menu-link:hover,
        .menu-button:hover {
            color: var(--red);
            background: #fff5f5;
        }

        .menu-link.active {
            color: var(--red);
            background: linear-gradient(90deg, #ffe4e6 0%, #fff7f7 100%);
        }

        .menu-link.active::before {
            content: "";
            position: absolute;
            left: -20px;
            width: 4px;
            height: 42px;
            border-radius: 999px;
            background: var(--red);
        }

        .menu-icon {
            display: grid;
            flex: 0 0 auto;
            width: 25px;
            height: 25px;
            place-items: center;
        }

        .sidebar-separator {
            height: 1px;
            margin: 28px 12px;
            background: var(--line);
        }

        .sidebar-bottom {
            position: absolute;
            right: 20px;
            bottom: 20px;
            left: 20px;
        }

        .quote-card {
            display: grid;
            place-items: center;
            min-height: 176px;
            margin-bottom: 18px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: var(--shadow-soft);
            text-align: center;
        }

        .quote-icon {
            display: grid;
            width: 74px;
            height: 74px;
            place-items: center;
            margin-bottom: 12px;
            color: var(--red);
            border-radius: 999px;
            background: var(--red-soft);
            font-size: 30px;
            font-weight: 900;
        }

        .quote-text {
            margin: 0;
            color: var(--red);
            font-size: 14px;
            font-weight: 800;
            line-height: 1.45;
        }

        .main {
            min-width: 0;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: grid;
            grid-template-columns: minmax(260px, 1fr) minmax(280px, 420px) auto;
            gap: 24px;
            align-items: center;
            min-height: 104px;
            padding: 22px 32px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
        }

        .page-title {
            margin: 0;
            font-size: 31px;
            font-weight: 900;
            letter-spacing: -0.045em;
        }

        .page-subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 12px;
            height: 54px;
            min-width: 0;
            padding: 0 18px;
            border: 1px solid #dbe1ea;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.03);
        }

        .search-box input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: none;
            color: var(--text);
            background: transparent;
            font-size: 14px;
        }

        .avatar {
            display: grid;
            flex: 0 0 auto;
            width: 52px;
            height: 52px;
            place-items: center;
            color: var(--red);
            border-radius: 999px;
            background: #ffe1e5;
            font-weight: 900;
        }

        .content {
            padding: 26px 32px 36px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            border: 1px solid var(--soft-line);
            border-radius: 20px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .summary-card {
            min-height: 118px;
            padding: 22px;
        }

        .summary-label {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 850;
        }

        .summary-value {
            margin: 8px 0 4px;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.05em;
        }

        .summary-note {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .quick-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px;
            border: 1px solid var(--soft-line);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: var(--shadow-soft);
        }

        .quick-icon {
            display: grid;
            flex: 0 0 auto;
            width: 46px;
            height: 46px;
            place-items: center;
            border-radius: 15px;
            font-weight: 900;
        }

        .quick-icon.red {
            color: var(--red);
            background: var(--red-soft);
        }

        .quick-icon.blue {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .quick-icon.green {
            color: var(--green);
            background: var(--green-soft);
        }

        .quick-title {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
        }

        .quick-description {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .panel {
            padding: 24px;
        }

        .panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .panel-title {
            margin: 0;
            font-size: 23px;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .panel-description {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .filter-bar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 220px 130px;
            gap: 12px;
            margin-bottom: 22px;
        }

        .filter-input,
        .filter-select {
            height: 48px;
            width: 100%;
            border: 1px solid #dbe1ea;
            border-radius: 12px;
            background: #ffffff;
            color: var(--text);
            outline: none;
            padding: 0 14px;
            font-size: 14px;
        }

        .filter-button {
            height: 48px;
            border: 0;
            border-radius: 12px;
            color: #ffffff;
            background: var(--red);
            font-weight: 900;
            cursor: pointer;
        }

        .timeline {
            position: relative;
            display: grid;
            gap: 16px;
        }

        .timeline::before {
            content: "";
            position: absolute;
            top: 8px;
            bottom: 8px;
            left: 23px;
            width: 2px;
            background: linear-gradient(
                180deg,
                var(--red-border),
                #dbeafe,
                #dcfce7
            );
        }

        .timeline-item {
            position: relative;
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            gap: 16px;
            align-items: start;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #ffffff;
        }

        .timeline-icon {
            z-index: 2;
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border-radius: 16px;
            font-weight: 900;
        }

        .timeline-icon.pengajuan {
            color: var(--red);
            background: var(--red-soft);
        }

        .timeline-icon.distribusi {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .timeline-content {
            min-width: 0;
        }

        .timeline-title-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .timeline-title {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            line-height: 1.45;
        }

        .timeline-description {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .timeline-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            color: #475569;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #ffffff;
            font-size: 12px;
            font-weight: 850;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
        }

        .badge-pengajuan {
            color: var(--red);
            background: var(--red-soft);
        }

        .badge-distribusi {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .badge-status {
            color: var(--green);
            background: var(--green-soft);
        }

        .timeline-side {
            display: grid;
            justify-items: end;
            gap: 10px;
            min-width: 240px;
        }

        .timeline-date {
            color: var(--muted);
            font-size: 13px;
            font-weight: 850;
            line-height: 1.55;
            text-align: right;
            white-space: nowrap;
        }

        .action-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .action-link.pengajuan {
            color: var(--red);
            background: var(--red-soft);
        }

        .action-link.distribusi {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .action-link.bukti {
            color: var(--green);
            background: var(--green-soft);
        }

        .action-link.unduh {
            color: var(--red);
            background: var(--red-soft);
        }

        .empty-state {
            padding: 36px;
            color: var(--muted);
            border: 1px dashed var(--red-border);
            border-radius: 16px;
            background: #fff7f7;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }

        @media (max-width: 1280px) {
            .summary-grid,
            .quick-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                grid-template-columns: 1fr;
            }

            .timeline-item {
                grid-template-columns: 48px minmax(0, 1fr);
            }

            .timeline-side {
                grid-column: 2;
                justify-items: start;
                min-width: 0;
            }

            .timeline-date {
                text-align: left;
            }

            .action-group {
                justify-content: flex-start;
            }
        }

        @media (max-width: 1080px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: relative;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .sidebar-bottom {
                position: static;
                margin-top: 28px;
            }

            .menu {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .menu-link.active::before {
                display: none;
            }

            .timeline::before {
                display: none;
            }
        }

        @media (max-width: 780px) {
            .content,
            .topbar {
                padding-right: 18px;
                padding-left: 18px;
            }

            .filter-bar {
                grid-template-columns: 1fr;
            }

            .menu {
                grid-template-columns: 1fr;
            }

            .timeline-item {
                grid-template-columns: 1fr;
            }

            .timeline-side {
                grid-column: 1;
            }
        }
    </style>
</head>

<body>
    @php
        $icon = fn (string $name): string => match ($name) {
            'drop' => '<svg viewBox="0 0 24 24"><path d="M12 2s7 7.2 7 12a7 7 0 0 1-14 0C5 9.2 12 2 12 2Z"></path><path d="M9.8 14.2 11.4 16l3.2-4"></path></svg>',
            'home' => '<svg viewBox="0 0 24 24"><path d="m3 11 9-8 9 8"></path><path d="M5 10v10h14V10"></path><path d="M9 20v-6h6v6"></path></svg>',
            'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path></svg>',
            'truck' => '<svg viewBox="0 0 24 24"><path d="M3 6h11v10H3z"></path><path d="M14 10h4l3 3v3h-7z"></path><circle cx="7" cy="18" r="2"></circle><circle cx="17" cy="18" r="2"></circle></svg>',
            'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>',
            'history' => '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 3v6h6"></path><path d="M12 7v5l3 2"></path></svg>',
            'help' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.1 9a3 3 0 1 1 5.8 1c-.6 1.5-2.9 1.8-2.9 4"></path><path d="M12 18h.01"></path></svg>',
            'settings' => '<svg viewBox="0 0 24 24"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"></path><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 1 1 4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1A2 2 0 1 1 7.1 4.2l.1.1a1.7 1.7 0 0 0 1.9.3h.1a1.7 1.7 0 0 0 .9-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1A2 2 0 1 1 19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.6.9h.1a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.6 1Z"></path></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path></svg>',
            'logout' => '<svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M21 3v18"></path></svg>',
            default => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle></svg>',
        };

        $namaPemohon = $profil?->nama_rumah_sakit ?? $pengguna->name;

        $inisial = collect(explode(' ', $namaPemohon))
            ->filter()
            ->take(2)
            ->map(fn ($item) => mb_substr($item, 0, 1))
            ->implode('');

        $inisial = filled($inisial)
            ? mb_strtoupper($inisial)
            : 'PD';

        $statusLabel = function ($status): string {
            if (is_object($status) && method_exists($status, 'label')) {
                return $status->label();
            }

            return \Illuminate\Support\Str::of((string) ($status instanceof \BackedEnum ? $status->value : $status))
                ->replace('_', ' ')
                ->replace('-', ' ')
                ->headline()
                ->toString();
        };

        $urlData = function (array $item): string {
            $jenis = $item['jenis'] ?? null;
            $nomor = $item['nomor'] ?? null;

            if ($jenis === 'pengajuan') {
                return route('pemohon-donor.pengajuan.index', [
                    'q' => $nomor,
                ]);
            }

            if ($jenis === 'distribusi') {
                return route('pemohon-donor.distribusi.index', [
                    'q' => $nomor,
                ]);
            }

            return route('pemohon-donor.riwayat.index');
        };

        $urlBukti = function (array $item): ?string {
            $jenis = $item['jenis'] ?? null;
            $nomor = $item['nomor'] ?? null;

            if (
                ! filled($jenis)
                || ! filled($nomor)
                || $nomor === '-'
                || ! \Illuminate\Support\Facades\Route::has('pemohon-donor.riwayat.bukti')
            ) {
                return null;
            }

            return route('pemohon-donor.riwayat.bukti', [
                'jenis' => $jenis,
                'nomor' => $nomor,
            ]);
        };

        $urlUnduhBukti = function (array $item): ?string {
            $jenis = $item['jenis'] ?? null;
            $nomor = $item['nomor'] ?? null;

            if (
                ! filled($jenis)
                || ! filled($nomor)
                || $nomor === '-'
                || ! \Illuminate\Support\Facades\Route::has('pemohon-donor.riwayat.bukti.unduh')
            ) {
                return null;
            }

            return route('pemohon-donor.riwayat.bukti.unduh', [
                'jenis' => $jenis,
                'nomor' => $nomor,
            ]);
        };
    @endphp

    <div class="app">
        
<aside class="sidebar">
            <a
                href="{{ route('pemohon-donor.beranda') }}"
                class="brand"
            >
                <span class="brand-mark">
                    {!! $icon('drop') !!}
                </span>

                <span class="brand-title">
                    Portal
                    <strong>Pemohon Donor</strong>
                </span>
            </a>

            <nav class="menu">
                <a
                    href="{{ route('pemohon-donor.beranda') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">{!! $icon('home') !!}</span>
                    Dashboard
                </a>

                <a
                    href="{{ route('pemohon-donor.pengajuan.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">{!! $icon('file') !!}</span>
                    Pengajuan
                </a>

                <a
                    href="{{ route('pemohon-donor.distribusi.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">{!! $icon('truck') !!}</span>
                    Distribusi
                </a>

                <a
                    href="{{ route('pemohon-donor.profil.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">{!! $icon('user') !!}</span>
                    Profil
                </a>

                <a
                    href="{{ route('pemohon-donor.riwayat.index') }}"
                    class="menu-link active"
                >
                    <span class="menu-icon">{!! $icon('history') !!}</span>
                    Riwayat
                </a>
            </nav>

            <div class="sidebar-separator"></div>

            <nav class="menu">
                <a
                    href="{{ route('pemohon-donor.bantuan.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">{!! $icon('help') !!}</span>
                    Bantuan
                </a>

                <a
                    href="{{ route('pemohon-donor.pengaturan.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">{!! $icon('settings') !!}</span>
                    Pengaturan
                </a>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="menu-button"
                    >
                        <span class="menu-icon">{!! $icon('logout') !!}</span>
                        Keluar
                    </button>
                </form>
            </nav>

            <div class="sidebar-bottom">
                <div class="quote-card">
                    <div class="quote-icon">
                        ♥
                    </div>

                    <p class="quote-text">
                        Setetes darah,<br>
                        sejuta harapan.
                    </p>
                </div>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div>
                    <h1 class="page-title">
                        Riwayat Aktivitas
                    </h1>

                    <p class="page-subtitle">
                        Pantau jejak pengajuan, distribusi, lihat bukti, dan unduh bukti langsung dari timeline.
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('pemohon-donor.riwayat.index') }}"
                    class="search-box"
                >
                    {!! $icon('search') !!}

                    <input
                        type="search"
                        name="q"
                        placeholder="Cari nomor pengajuan atau distribusi..."
                        value="{{ $q }}"
                    >

                    @if (filled($jenisAktif))
                        <input
                            type="hidden"
                            name="jenis"
                            value="{{ $jenisAktif }}"
                        >
                    @endif
                </form>

                <div class="avatar">
                    {{ $inisial }}
                </div>
            </header>

            <section class="content">
                <section class="summary-grid">
                    <article class="card summary-card">
                        <p class="summary-label">
                            Total Riwayat
                        </p>

                        <p class="summary-value">
                            {{ number_format($totalRiwayat) }}
                        </p>

                        <p class="summary-note">
                            Aktivitas yang sedang ditampilkan
                        </p>
                    </article>

                    <article class="card summary-card">
                        <p class="summary-label">
                            Riwayat Pengajuan
                        </p>

                        <p class="summary-value">
                            {{ number_format($totalPengajuan) }}
                        </p>

                        <p class="summary-note">
                            Pembaruan kebutuhan donor
                        </p>
                    </article>

                    <article class="card summary-card">
                        <p class="summary-label">
                            Riwayat Distribusi
                        </p>

                        <p class="summary-value">
                            {{ number_format($totalDistribusi) }}
                        </p>

                        <p class="summary-note">
                            Pembaruan distribusi kantong darah
                        </p>
                    </article>
                </section>

                <section class="quick-grid">
                    <a
                        href="{{ route('pemohon-donor.pengajuan.index') }}"
                        class="quick-card"
                    >
                        <span class="quick-icon red">
                            P
                        </span>

                        <span>
                            <p class="quick-title">
                                Buka Pengajuan
                            </p>

                            <p class="quick-description">
                                Lihat seluruh pengajuan kebutuhan donor.
                            </p>
                        </span>
                    </a>

                    <a
                        href="{{ route('pemohon-donor.distribusi.index') }}"
                        class="quick-card"
                    >
                        <span class="quick-icon blue">
                            D
                        </span>

                        <span>
                            <p class="quick-title">
                                Buka Distribusi
                            </p>

                            <p class="quick-description">
                                Pantau jadwal dan bukti distribusi.
                            </p>
                        </span>
                    </a>

                    <a
                        href="{{ route('pemohon-donor.pengajuan.create') }}"
                        class="quick-card"
                    >
                        <span class="quick-icon green">
                            +
                        </span>

                        <span>
                            <p class="quick-title">
                                Buat Pengajuan Baru
                            </p>

                            <p class="quick-description">
                                Ajukan kebutuhan donor baru.
                            </p>
                        </span>
                    </a>
                </section>

                <section class="card panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                Timeline Aktivitas
                            </h2>

                            <p class="panel-description">
                                Gunakan tombol Lihat Bukti atau Unduh Bukti sesuai jenis aktivitas pengajuan dan distribusi.
                            </p>
                        </div>
                    </div>

                    <form
                        method="GET"
                        action="{{ route('pemohon-donor.riwayat.index') }}"
                        class="filter-bar"
                    >
                        <input
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            class="filter-input"
                            placeholder="Cari nomor atau referensi..."
                        >

                        <select
                            name="jenis"
                            class="filter-select"
                        >
                            <option value="">
                                Semua Jenis
                            </option>

                            <option
                                value="pengajuan"
                                @selected($jenisAktif === 'pengajuan')
                            >
                                Pengajuan
                            </option>

                            <option
                                value="distribusi"
                                @selected($jenisAktif === 'distribusi')
                            >
                                Distribusi
                            </option>
                        </select>

                        <button
                            type="submit"
                            class="filter-button"
                        >
                            Filter
                        </button>
                    </form>

                    @if ($riwayat->isNotEmpty())
                        <div class="timeline">
                            @foreach ($riwayat as $item)
                                @php
                                    $jenis = $item['jenis'] ?? 'pengajuan';
                                    $waktu = $item['waktu'] ?? null;
                                    $nomor = $item['nomor'] ?? '-';
                                    $keterangan = $item['keterangan'] ?? '-';
                                    $judul = $item['judul'] ?? 'Aktivitas';
                                    $deskripsi = $item['deskripsi'] ?? '-';
                                    $status = $item['status'] ?? '-';
                                    $buktiUrl = $urlBukti($item);
                                    $unduhBuktiUrl = $urlUnduhBukti($item);
                                @endphp

                                <article class="timeline-item">
                                    <div class="timeline-icon {{ $jenis }}">
                                        {{ $jenis === 'pengajuan' ? 'P' : 'D' }}
                                    </div>

                                    <div class="timeline-content">
                                        <div class="timeline-title-row">
                                            <h3 class="timeline-title">
                                                {{ $judul }}
                                            </h3>

                                            <span class="badge {{ $jenis === 'pengajuan' ? 'badge-pengajuan' : 'badge-distribusi' }}">
                                                {{ \Illuminate\Support\Str::of($jenis)->headline() }}
                                            </span>

                                            <span class="badge badge-status">
                                                {{ $statusLabel($status) }}
                                            </span>
                                        </div>

                                        <p class="timeline-description">
                                            {{ $deskripsi }}
                                        </p>

                                        <div class="timeline-meta">
                                            <span class="meta-pill">
                                                Nomor: {{ $nomor }}
                                            </span>

                                            <span class="meta-pill">
                                                Referensi: {{ $keterangan }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="timeline-side">
                                        <div class="timeline-date">
                                            {{ $waktu?->format('d M Y') ?? '-' }}
                                            <br>
                                            {{ $waktu?->format('H:i') ?? '-' }}
                                        </div>

                                        <div class="action-group">
                                            <a
                                                href="{{ $urlData($item) }}"
                                                class="action-link {{ $jenis }}"
                                            >
                                                Buka Data
                                            </a>

                                            @if ($buktiUrl)
                                                <a
                                                    href="{{ $buktiUrl }}"
                                                    class="action-link bukti"
                                                >
                                                    Lihat Bukti
                                                </a>
                                            @endif

                                            @if ($unduhBuktiUrl)
                                                <a
                                                    href="{{ $unduhBuktiUrl }}"
                                                    class="action-link unduh"
                                                >
                                                    Unduh Bukti
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            Belum ada riwayat aktivitas yang sesuai dengan filter.
                        </div>
                    @endif
                </section>
            </section>
        </main>
    </div>
</body>
</html>