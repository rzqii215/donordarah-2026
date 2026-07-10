<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Buat Pengajuan Kebutuhan Donor</title>

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
            --danger: #dc2626;
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
        select,
        textarea {
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
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

        .avatar {
            display: grid;
            width: 52px;
            height: 52px;
            place-items: center;
            color: var(--red);
            border-radius: 999px;
            background: #ffe1e5;
            font-weight: 900;
        }

        .content {
            padding: 30px 32px 40px;
        }

        .layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 24px;
            align-items: start;
        }

        .card {
            border: 1px solid var(--soft-line);
            border-radius: 22px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .form-card {
            padding: 28px;
        }

        .side-card {
            padding: 24px;
        }

        .section-title {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .section-description {
            margin: 8px 0 24px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .alert {
            margin-bottom: 20px;
            padding: 16px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 750;
            line-height: 1.6;
        }

        .alert-danger {
            color: #991b1b;
            border: 1px solid #fecaca;
            background: #fef2f2;
        }

        .alert-warning {
            color: #92400e;
            border: 1px solid #fed7aa;
            background: #fff7ed;
        }

        .alert ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .form-section {
            margin-top: 26px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .form-section:first-of-type {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .form-section-title {
            margin: 0 0 16px;
            font-size: 18px;
            font-weight: 900;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .form-group {
            display: grid;
            gap: 8px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            color: var(--text);
            font-size: 14px;
            font-weight: 850;
        }

        .form-control {
            width: 100%;
            min-height: 50px;
            padding: 0 14px;
            color: var(--text);
            border: 1px solid #dbe1ea;
            border-radius: 13px;
            outline: none;
            background: #ffffff;
            font-size: 14px;
        }

        textarea.form-control {
            min-height: 132px;
            padding-top: 13px;
            resize: vertical;
        }

        .form-control:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 4px rgba(239, 29, 38, 0.08);
        }

        .form-hint {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .form-error {
            color: var(--danger);
            font-size: 12px;
            font-weight: 800;
        }

        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .button-primary,
        .button-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 52px;
            padding: 0 20px;
            border-radius: 13px;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
        }

        .button-primary {
            color: #ffffff;
            border: 0;
            background: linear-gradient(135deg, var(--red), #ef4444);
            box-shadow: 0 16px 28px rgba(239, 29, 38, 0.2);
        }

        .button-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: none;
        }

        .button-secondary {
            color: var(--red);
            border: 1px solid var(--red-border);
            background: #ffffff;
        }

        .profile-card {
            display: grid;
            gap: 16px;
        }

        .profile-main {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--line);
        }

        .profile-name {
            margin: 0;
            font-size: 17px;
            font-weight: 900;
            line-height: 1.4;
        }

        .profile-email {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .info-list {
            display: grid;
            gap: 13px;
        }

        .info-item {
            padding: 15px;
            border: 1px solid var(--soft-line);
            border-radius: 15px;
            background: #ffffff;
        }

        .info-label {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .info-value {
            margin: 6px 0 0;
            font-size: 14px;
            font-weight: 850;
            line-height: 1.5;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
        }

        .status-success {
            color: var(--green);
            background: var(--green-soft);
        }

        .status-warning {
            color: #d97706;
            background: var(--yellow-soft);
        }

        .step-list {
            display: grid;
            gap: 12px;
        }

        .step-item {
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #ffffff;
        }

        .step-number {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            color: var(--red);
            border-radius: 12px;
            background: var(--red-soft);
            font-size: 13px;
            font-weight: 900;
        }

        .step-title {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            line-height: 1.45;
        }

        .step-description {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        @media (max-width: 1180px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 980px) {
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

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 720px) {
            .content,
            .topbar {
                padding-right: 18px;
                padding-left: 18px;
            }

            .form-grid {
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
            'save' => '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path><path d="M17 21v-8H7v8"></path><path d="M7 3v5h8"></path></svg>',
            'plus-drop' => '<svg viewBox="0 0 24 24"><path d="M12 2s7 7.2 7 12a7 7 0 0 1-14 0C5 9.2 12 2 12 2Z"></path><path d="M12 9v6"></path><path d="M9 12h6"></path></svg>',
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

        $labelEnum = function ($enum): string {
            if (is_object($enum) && method_exists($enum, 'label')) {
                return $enum->label();
            }

            return str((string) ($enum instanceof \BackedEnum ? $enum->value : $enum))
                ->replace('_', ' ')
                ->replace('-', ' ')
                ->headline()
                ->toString();
        };

        $rhesusLabel = function ($enum): string {
            if (is_object($enum) && method_exists($enum, 'simbol')) {
                return $enum->simbol();
            }

            if (is_object($enum) && method_exists($enum, 'label')) {
                return $enum->label();
            }

            return (string) ($enum instanceof \BackedEnum ? $enum->value : $enum);
        };

        $profilLengkap = $profil !== null;
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
                    class="menu-link active"
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
                    class="menu-link"
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
                        Buat Pengajuan
                    </h1>

                    <p class="page-subtitle">
                        Isi kebutuhan donor dengan data yang jelas agar mudah diproses petugas.
                    </p>
                </div>

                <div class="avatar">
                    {{ $inisial }}
                </div>
            </header>

            <section class="content">
                <div class="layout-grid">
                    <article class="card form-card">
                        <h2 class="section-title">
                            Form Pengajuan Kebutuhan Donor
                        </h2>

                        <p class="section-description">
                            Pastikan data golongan darah, jumlah kantong, tingkat urgensi, dan waktu kebutuhan sudah benar sebelum dikirim.
                        </p>

                        @if (! $profilLengkap)
                            <div class="alert alert-warning">
                                Profil Pemohon Donor belum tersedia. Lengkapi profil terlebih dahulu sebelum membuat pengajuan.
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                Data belum bisa dikirim. Periksa kembali isian berikut:

                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>
                                            {{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('pemohon-donor.pengajuan.store') }}"
                            enctype="multipart/form-data"
                        >
                            @csrf

                            <section class="form-section">
                                <h3 class="form-section-title">
                                    Informasi Pengajuan
                                </h3>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label
                                            for="referensi_pasien"
                                            class="form-label"
                                        >
                                            Referensi Pengajuan
                                        </label>

                                        <input
                                            id="referensi_pasien"
                                            name="referensi_pasien"
                                            type="text"
                                            class="form-control"
                                            value="{{ old('referensi_pasien') }}"
                                            placeholder="Contoh: PGJ-RAWAT-0001"
                                            required
                                            @disabled(! $profilLengkap)
                                        >

                                        <span class="form-hint">
                                            Bisa diisi nomor rujukan, kode kebutuhan, atau referensi internal pemohon.
                                        </span>

                                        @error('referensi_pasien')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="nama_dokter"
                                            class="form-label"
                                        >
                                            Penanggung Jawab Pengajuan
                                        </label>

                                        <input
                                            id="nama_dokter"
                                            name="nama_dokter"
                                            type="text"
                                            class="form-control"
                                            value="{{ old('nama_dokter') }}"
                                            placeholder="Contoh: dr. Ahmad / Koordinator Donor"
                                            required
                                            @disabled(! $profilLengkap)
                                        >

                                        @error('nama_dokter')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="form-section">
                                <h3 class="form-section-title">
                                    Kebutuhan Darah
                                </h3>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label
                                            for="golongan_darah"
                                            class="form-label"
                                        >
                                            Golongan Darah
                                        </label>

                                        <select
                                            id="golongan_darah"
                                            name="golongan_darah"
                                            class="form-control"
                                            required
                                            @disabled(! $profilLengkap)
                                        >
                                            <option value="">
                                                Pilih Golongan Darah
                                            </option>

                                            @foreach ($golonganOptions as $golongan)
                                                <option
                                                    value="{{ $golongan->value }}"
                                                    @selected(old('golongan_darah') === $golongan->value)
                                                >
                                                    {{ $labelEnum($golongan) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('golongan_darah')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="rhesus"
                                            class="form-label"
                                        >
                                            Rhesus
                                        </label>

                                        <select
                                            id="rhesus"
                                            name="rhesus"
                                            class="form-control"
                                            required
                                            @disabled(! $profilLengkap)
                                        >
                                            <option value="">
                                                Pilih Rhesus
                                            </option>

                                            @foreach ($rhesusOptions as $rhesus)
                                                <option
                                                    value="{{ $rhesus->value }}"
                                                    @selected(old('rhesus') === $rhesus->value)
                                                >
                                                    {{ $rhesusLabel($rhesus) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('rhesus')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="jumlah_kantong"
                                            class="form-label"
                                        >
                                            Jumlah Kantong
                                        </label>

                                        <input
                                            id="jumlah_kantong"
                                            name="jumlah_kantong"
                                            type="number"
                                            min="1"
                                            max="100"
                                            class="form-control"
                                            value="{{ old('jumlah_kantong', 1) }}"
                                            required
                                            @disabled(! $profilLengkap)
                                        >

                                        @error('jumlah_kantong')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="tingkat_urgensi"
                                            class="form-label"
                                        >
                                            Tingkat Urgensi
                                        </label>

                                        <select
                                            id="tingkat_urgensi"
                                            name="tingkat_urgensi"
                                            class="form-control"
                                            required
                                            @disabled(! $profilLengkap)
                                        >
                                            <option value="">
                                                Pilih Urgensi
                                            </option>

                                            @foreach ($urgensiOptions as $urgensi)
                                                <option
                                                    value="{{ $urgensi->value }}"
                                                    @selected(old('tingkat_urgensi') === $urgensi->value)
                                                >
                                                    {{ $labelEnum($urgensi) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('tingkat_urgensi')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group full">
                                        <label
                                            for="dibutuhkan_pada"
                                            class="form-label"
                                        >
                                            Dibutuhkan Pada
                                        </label>

                                        <input
                                            id="dibutuhkan_pada"
                                            name="dibutuhkan_pada"
                                            type="datetime-local"
                                            class="form-control"
                                            value="{{ old('dibutuhkan_pada') }}"
                                            required
                                            @disabled(! $profilLengkap)
                                        >

                                        <span class="form-hint">
                                            Pilih tanggal dan jam kebutuhan donor.
                                        </span>

                                        @error('dibutuhkan_pada')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="form-section">
                                <h3 class="form-section-title">
                                    Dokumen dan Catatan
                                </h3>

                                <div class="form-grid">
                                    <div class="form-group full">
                                        <label
                                            for="dokumen_permintaan"
                                            class="form-label"
                                        >
                                            Dokumen Pendukung
                                        </label>

                                        <input
                                            id="dokumen_permintaan"
                                            name="dokumen_permintaan"
                                            type="file"
                                            class="form-control"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            @disabled(! $profilLengkap)
                                        >

                                        <span class="form-hint">
                                            Opsional. Format PDF, JPG, JPEG, atau PNG. Maksimal 4 MB.
                                        </span>

                                        @error('dokumen_permintaan')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group full">
                                        <label
                                            for="catatan"
                                            class="form-label"
                                        >
                                            Catatan Tambahan
                                        </label>

                                        <textarea
                                            id="catatan"
                                            name="catatan"
                                            class="form-control"
                                            placeholder="Tuliskan catatan tambahan agar petugas lebih mudah memahami kebutuhan pengajuan."
                                            @disabled(! $profilLengkap)
                                        >{{ old('catatan') }}</textarea>

                                        @error('catatan')
                                            <span class="form-error">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <div class="button-row">
                                <button
                                    type="submit"
                                    class="button-primary"
                                    @disabled(! $profilLengkap)
                                >
                                    {!! $icon('plus-drop') !!}
                                    Kirim Pengajuan
                                </button>

                                <a
                                    href="{{ route('pemohon-donor.pengajuan.index') }}"
                                    class="button-secondary"
                                >
                                    Kembali ke Pengajuan
                                </a>

                                @if (! $profilLengkap)
                                    <a
                                        href="{{ route('pemohon-donor.profil.index') }}"
                                        class="button-secondary"
                                    >
                                        Lengkapi Profil
                                    </a>
                                @endif
                            </div>
                        </form>
                    </article>

                    <aside class="card side-card">
                        <div class="profile-card">
                            <div class="profile-main">
                                <div class="avatar">
                                    {{ $inisial }}
                                </div>

                                <div>
                                    <p class="profile-name">
                                        {{ $namaPemohon }}
                                    </p>

                                    <p class="profile-email">
                                        {{ $pengguna->email }}
                                    </p>
                                </div>
                            </div>

                            <div class="info-list">
                                <div class="info-item">
                                    <p class="info-label">
                                        Status Profil
                                    </p>

                                    <p class="info-value">
                                        @if ($profilLengkap)
                                            <span class="status-pill status-success">
                                                Siap Mengajukan
                                            </span>
                                        @else
                                            <span class="status-pill status-warning">
                                                Belum Lengkap
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <div class="info-item">
                                    <p class="info-label">
                                        Kode Pemohon
                                    </p>

                                    <p class="info-value">
                                        {{ $profil?->kode_rumah_sakit ?? '-' }}
                                    </p>
                                </div>

                                <div class="info-item">
                                    <p class="info-label">
                                        Penanggung Jawab
                                    </p>

                                    <p class="info-value">
                                        {{ $profil?->nama_penanggung_jawab ?? '-' }}
                                    </p>
                                </div>

                                <div class="info-item">
                                    <p class="info-label">
                                        Alamat
                                    </p>

                                    <p class="info-value">
                                        {{ $profil?->alamat ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="step-list">
                                <article class="step-item">
                                    <div class="step-number">1</div>

                                    <div>
                                        <p class="step-title">
                                            Lengkapi data kebutuhan
                                        </p>

                                        <p class="step-description">
                                            Pilih golongan darah, rhesus, jumlah kantong, dan tingkat urgensi.
                                        </p>
                                    </div>
                                </article>

                                <article class="step-item">
                                    <div class="step-number">2</div>

                                    <div>
                                        <p class="step-title">
                                            Kirim pengajuan
                                        </p>

                                        <p class="step-description">
                                            Pengajuan akan masuk ke daftar untuk diverifikasi petugas.
                                        </p>
                                    </div>
                                </article>

                                <article class="step-item">
                                    <div class="step-number">3</div>

                                    <div>
                                        <p class="step-title">
                                            Pantau status
                                        </p>

                                        <p class="step-description">
                                            Status dapat dilihat melalui halaman Pengajuan dan Riwayat.
                                        </p>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </main>
    </div>
</body>
</html>