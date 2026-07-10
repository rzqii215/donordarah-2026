<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Distribusi Kantong Darah</title>

    <style>
        :root {
            --red: #ef1d26;
            --red-soft: #fff1f2;
            --red-border: #fecdd3;
            --blue: #2563eb;
            --blue-soft: #dbeafe;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --orange: #f97316;
            --orange-soft: #ffedd5;
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

        .brand-mark svg {
            width: 27px;
            height: 27px;
            fill: currentColor;
            stroke: none;
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

        .top-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
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

        .user-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .user-info {
            min-width: 0;
            max-width: 180px;
        }

        .user-info strong {
            display: block;
            overflow: hidden;
            font-size: 14px;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-info span {
            color: var(--muted);
            font-size: 13px;
        }

        .content {
            padding: 26px 32px 32px;
        }

        .alert {
            margin-bottom: 20px;
            padding: 16px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 750;
            line-height: 1.6;
        }

        .alert-success {
            color: #166534;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
        }

        .alert-danger {
            color: #991b1b;
            border: 1px solid #fecaca;
            background: #fef2f2;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
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
            grid-template-columns: minmax(0, 1fr) 240px 130px;
            gap: 12px;
            margin-bottom: 20px;
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

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 16px;
        }

        .data-table {
            width: 100%;
            min-width: 1220px;
            border-collapse: collapse;
        }

        .data-table th {
            padding: 15px 16px;
            color: #64748b;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
            font-weight: 800;
            text-align: left;
            white-space: nowrap;
        }

        .data-table td {
            padding: 17px 16px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: 0;
        }

        .main-text {
            display: block;
            color: var(--text);
            font-weight: 900;
            line-height: 1.35;
        }

        .sub-text {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 13px;
        }

        .status-badge,
        .blood-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 13px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 900;
            white-space: nowrap;
        }

        .blood-badge {
            color: var(--red);
            border: 1px solid var(--red-border);
            background: #fff5f5;
        }

        .status-warning {
            color: #d97706;
            background: var(--yellow-soft);
        }

        .status-info {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .status-success {
            color: var(--green);
            background: var(--green-soft);
        }

        .status-danger {
            color: var(--red);
            background: #fee2e2;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 11px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 900;
        }

        .action-link.view {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .action-link.download {
            color: var(--red);
            background: var(--red-soft);
        }

        .action-link.disabled {
            color: #94a3b8;
            background: #f1f5f9;
            cursor: not-allowed;
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
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .topbar {
                grid-template-columns: 1fr;
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
        }

        @media (max-width: 780px) {
            .content,
            .topbar {
                padding-right: 18px;
                padding-left: 18px;
            }

            .summary-grid,
            .filter-bar {
                grid-template-columns: 1fr;
            }

            .menu {
                grid-template-columns: 1fr;
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
            'logout' => '<svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M21 3v18"></path></svg>',
            'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path></svg>',
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

            return str((string) ($status instanceof \BackedEnum ? $status->value : $status))
                ->replace('_', ' ')
                ->replace('-', ' ')
                ->headline()
                ->toString();
        };

        $statusClass = function ($status): string {
            $value = $status instanceof \BackedEnum
                ? $status->value
                : (string) $status;

            return match ($value) {
                'dijadwalkan',
                'scheduled' => 'status-warning',

                'siap_diserahkan',
                'ready' => 'status-info',

                'selesai',
                'completed' => 'status-success',

                'dibatalkan',
                'cancelled' => 'status-danger',

                default => 'status-info',
            };
        };

        $golonganLabel = function ($golongan): string {
            if (is_object($golongan) && method_exists($golongan, 'label')) {
                return $golongan->label();
            }

            return (string) ($golongan instanceof \BackedEnum ? $golongan->value : $golongan);
        };

        $rhesusSimbol = function ($rhesus): string {
            if (is_object($rhesus) && method_exists($rhesus, 'simbol')) {
                return $rhesus->simbol();
            }

            return (string) ($rhesus instanceof \BackedEnum ? $rhesus->value : $rhesus);
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
                    <span class="menu-icon">
                        {!! $icon('home') !!}
                    </span>
                    Dashboard
                </a>

                <a
                    href="{{ route('pemohon-donor.pengajuan.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">
                        {!! $icon('file') !!}
                    </span>
                    Pengajuan
                </a>

                <a
                    href="{{ route('pemohon-donor.distribusi.index') }}"
                    class="menu-link active"
                >
                    <span class="menu-icon">
                        {!! $icon('truck') !!}
                    </span>
                    Distribusi
                </a>

                <a
                    href="{{ route('pemohon-donor.profil.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">
                        {!! $icon('user') !!}
                    </span>
                    Profil
                </a>

                <a
                    href="{{ route('pemohon-donor.riwayat.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">
                        {!! $icon('history') !!}
                    </span>
                    Riwayat
                </a>
            </nav>

            <div class="sidebar-separator"></div>

            <nav class="menu">
                <a
                    href="{{ route('pemohon-donor.bantuan.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">
                        {!! $icon('help') !!}
                    </span>
                    Bantuan
                </a>

                <a
                    href="{{ route('pemohon-donor.pengaturan.index') }}"
                    class="menu-link"
                >
                    <span class="menu-icon">
                        {!! $icon('settings') !!}
                    </span>
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
                        <span class="menu-icon">
                            {!! $icon('logout') !!}
                        </span>
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
                        Distribusi Kantong Darah
                    </h1>

                    <p class="page-subtitle">
                        Pantau jadwal, status, dan bukti distribusi kantong darah.
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('pemohon-donor.distribusi.index') }}"
                    class="search-box"
                >
                    {!! $icon('search') !!}

                    <input
                        type="search"
                        name="q"
                        placeholder="Cari nomor distribusi atau nomor pengajuan..."
                        value="{{ $q }}"
                    >

                    @if (filled($statusAktif))
                        <input
                            type="hidden"
                            name="status"
                            value="{{ $statusAktif }}"
                        >
                    @endif
                </form>

                <div class="top-actions">
                    <div class="user-chip">
                        <div class="avatar">
                            {{ $inisial }}
                        </div>

                        <div class="user-info">
                            <strong>
                                {{ $namaPemohon }}
                            </strong>

                            <span>
                                Pemohon
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <section class="content">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <section class="summary-grid">
                    <article class="card summary-card">
                        <p class="summary-label">
                            Total Distribusi
                        </p>

                        <p class="summary-value">
                            {{ number_format($totalDistribusi) }}
                        </p>

                        <p class="summary-note">
                            Seluruh data distribusi
                        </p>
                    </article>

                    <article class="card summary-card">
                        <p class="summary-label">
                            Dijadwalkan
                        </p>

                        <p class="summary-value">
                            {{ number_format($terjadwal) }}
                        </p>

                        <p class="summary-note">
                            Menunggu jadwal penyerahan
                        </p>
                    </article>

                    <article class="card summary-card">
                        <p class="summary-label">
                            Siap Diserahkan
                        </p>

                        <p class="summary-value">
                            {{ number_format($siapDiserahkan) }}
                        </p>

                        <p class="summary-note">
                            Kantong darah siap diserahkan
                        </p>
                    </article>

                    <article class="card summary-card">
                        <p class="summary-label">
                            Selesai
                        </p>

                        <p class="summary-value">
                            {{ number_format($selesai) }}
                        </p>

                        <p class="summary-note">
                            Distribusi selesai
                        </p>
                    </article>

                    <article class="card summary-card">
                        <p class="summary-label">
                            Dibatalkan
                        </p>

                        <p class="summary-value">
                            {{ number_format($dibatalkan) }}
                        </p>

                        <p class="summary-note">
                            Distribusi tidak dilanjutkan
                        </p>
                    </article>
                </section>

                <section class="card panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                Daftar Distribusi
                            </h2>

                            <p class="panel-description">
                                Tombol aksi pada tabel ini sekarang memakai bukti distribusi khusus, bukan bukti pengajuan.
                            </p>
                        </div>
                    </div>

                    <form
                        method="GET"
                        action="{{ route('pemohon-donor.distribusi.index') }}"
                        class="filter-bar"
                    >
                        <input
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            class="filter-input"
                            placeholder="Cari nomor distribusi atau pengajuan..."
                        >

                        <select
                            name="status"
                            class="filter-select"
                        >
                            <option value="">
                                Semua Status
                            </option>

                            @foreach ($statusOptions as $status)
                                <option
                                    value="{{ $status->value }}"
                                    @selected($statusAktif === $status->value)
                                >
                                    {{ is_object($status) && method_exists($status, 'label') ? $status->label() : $status->value }}
                                </option>
                            @endforeach
                        </select>

                        <button
                            type="submit"
                            class="filter-button"
                        >
                            Filter
                        </button>
                    </form>

                    @if ($distribusi->isNotEmpty())
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nomor Distribusi</th>
                                        <th>Pengajuan</th>
                                        <th>Gol. Darah</th>
                                        <th>Jumlah</th>
                                        <th>Jadwal</th>
                                        <th>Status</th>
                                        <th>Penerima</th>
                                        <th>Diserahkan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($distribusi as $item)
                                        @php
                                            $permintaan = $item->permintaan;
                                        @endphp

                                        <tr>
                                            <td>
                                                <span class="main-text">
                                                    {{ $item->nomor_distribusi ?? '-' }}
                                                </span>

                                                <span class="sub-text">
                                                    Distribusi Kantong Darah
                                                </span>
                                            </td>

                                            <td>
                                                <span class="main-text">
                                                    {{ $permintaan?->nomor_permintaan ?? '-' }}
                                                </span>

                                                <span class="sub-text">
                                                    {{ $permintaan?->referensi_pasien ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                @if ($permintaan)
                                                    <span class="blood-badge">
                                                        {{ $golonganLabel($permintaan->golongan_darah) }}{{ $rhesusSimbol($permintaan->rhesus) }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>
                                                {{ number_format($permintaan?->jumlah_kantong ?? 0) }} kantong
                                            </td>

                                            <td>
                                                <span class="main-text">
                                                    {{ $item->dijadwalkan_pada?->format('d M Y') ?? '-' }}
                                                </span>

                                                <span class="sub-text">
                                                    {{ $item->dijadwalkan_pada?->format('H:i') ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="status-badge {{ $statusClass($item->status) }}">
                                                    {{ $statusLabel($item->status) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="main-text">
                                                    {{ $item->nama_penerima ?? '-' }}
                                                </span>

                                                <span class="sub-text">
                                                    {{ $item->jabatan_penerima ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="main-text">
                                                    {{ $item->diserahkan_pada?->format('d M Y') ?? '-' }}
                                                </span>

                                                <span class="sub-text">
                                                    {{ $item->diserahkan_pada?->format('H:i') ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="action-group">
                                                    @if ($permintaan)
                                                        <a
                                                            href="{{ route('pemohon-donor.distribusi.bukti', $item) }}"
                                                            class="action-link view"
                                                        >
                                                            Lihat
                                                        </a>

                                                        <a
                                                            href="{{ route('pemohon-donor.distribusi.bukti.unduh', $item) }}"
                                                            class="action-link download"
                                                        >
                                                            Unduh
                                                        </a>
                                                    @else
                                                        <span class="action-link disabled">
                                                            Tidak tersedia
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            Belum ada data distribusi untuk Pemohon Donor ini.
                        </div>
                    @endif
                </section>
            </section>
        </main>
    </div>
</body>
</html>