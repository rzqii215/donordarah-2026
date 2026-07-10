<div class="login-page">
    
@php
        $googleRouteAda = \Illuminate\Support\Facades\Route::has('google.redirect');

        $googleUrl = $googleRouteAda
            ? route('google.redirect', ['tujuan' => 'login'])
            : '#';
    @endphp

    <section class="login-visual">
        <div class="login-logo">
            <div class="login-logo-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.4"
                >
                    <path d="M12 3s7 7.2 7 12a7 7 0 0 1-14 0c0-4.8 7-12 7-12Z" />
                    <path d="M9 15a3 3 0 0 0 3 3" />
                </svg>
            </div>

            <div>
                <strong>Donor</strong>
                <span>Darah</span>
            </div>
        </div>

        <div class="login-visual-content">
            <p>Modern Medical Care</p>

            <h1>
                Setetes Darah Anda,
                Sejuta Harapan Mereka
            </h1>

            <span>
                Masuk ke portal untuk melihat jadwal donor, lokasi kegiatan,
                stok darah, riwayat donor, atau memantau pengajuan kebutuhan darah.
            </span>

            <div class="login-feature-list">
                <article>
                    <span>
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M8 2v4" />
                            <path d="M16 2v4" />
                            <rect x="3" y="4" width="18" height="18" rx="3" />
                            <path d="M3 10h18" />
                        </svg>
                    </span>

                    Temukan jadwal donor terdekat
                </article>

                <article>
                    <span>
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z" />
                            <circle cx="12" cy="10" r="2" />
                        </svg>
                    </span>

                    Akses informasi lokasi donor
                </article>

                <article>
                    <span>
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M12 3s6 6.2 6 10.5a6 6 0 0 1-12 0C6 9.2 12 3 12 3Z" />
                        </svg>
                    </span>

                    Pantau stok dan riwayat donor
                </article>
            </div>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-card">
            <div class="login-heading">
                <p>Selamat Datang</p>

                <h2>
                    Masuk ke akun Anda
                </h2>

                <span>
                    Gunakan akun Google atau email dan kata sandi yang telah terdaftar.
                </span>
            </div>

            @if (session('success'))
                <div class="login-alert is-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('email'))
                <div class="login-alert is-danger">
                    {{ $errors->first('email') }}

                    @if (str_contains($errors->first('email'), 'belum terdaftar'))
                        <div class="login-alert-actions">
                            <a href="{{ url('/register/donor') }}">
                                Daftar Pendonor
                            </a>

                            <a href="{{ url('/register/pemohon-donor') }}">
                                Daftar Pemohon Donor
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <a href="{{ $googleUrl }}" class="login-google-button">
                <span>
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.651 32.657 29.223 36 24 36c-6.627 0-12-5.373-12-12S17.373 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917Z"/>
                        <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691Z"/>
                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.197l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.617-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44Z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917Z"/>
                    </svg>
                </span>

                Masuk dengan Google
            </a>

            <div class="login-divider">
                <span></span>
                <p>atau masuk manual</p>
                <span></span>
            </div>

            <form wire:submit="authenticate" class="login-form">
<label class="login-field">
                    <span>Alamat Email</span>

                    <div>
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M4 4h16v16H4z" />
                            <path d="m22 6-10 7L2 6" />
                        </svg>

                        <input
                            type="email"
                            wire:model="email"
                            placeholder="nama@email.com"
                            autocomplete="email"
                        >
                    </div>

                    @error('email')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="login-field">
                    <span>Kata Sandi</span>

                    <div>
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <rect x="5" y="11" width="14" height="10" rx="2" />
                            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                        </svg>

                        <input
                            type="password"
                            wire:model="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                        >
                    </div>

                    @error('password')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <div class="login-form-extra">
                    <label class="login-remember">
                        <input
                            type="checkbox"
                            wire:model="remember"
                        >

                        <span>Ingat saya</span>
                    </label>

                    <a href="{{ url('/forgot-password') }}">
                        Lupa kata sandi?
                    </a>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="login-submit"
                >
                    <span wire:loading.remove>
                        Masuk
                    </span>

                    <span wire:loading>
                        Memproses...
                    </span>
                </button>
            </form>

            <div class="login-register-box">
                <strong>
                    Belum punya akun?
                </strong>

                <a href="{{ url('/register/donor') }}">
                    Daftar sebagai Pendonor
                </a>

                <a href="{{ url('/register/pemohon-donor') }}" class="is-outline">
                    Daftar sebagai Pemohon Donor
                </a>
            </div>

            <p class="login-note">
                Data akun dilindungi dan hanya digunakan untuk pelayanan donor darah.
            </p>
        </div>
    </section>

    <style>
        .login-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(420px, 0.85fr);
            background: #ffffff;
        }

        .login-visual {
            position: relative;
            overflow: hidden;
            padding: 56px 64px;
            background:
                radial-gradient(circle at 84% 72%, rgba(239, 68, 68, 0.13), transparent 24rem),
                linear-gradient(135deg, #fff1f2 0%, #ffffff 72%);
        }

        .login-visual::before {
            content: "";
            position: absolute;
            right: -140px;
            bottom: -180px;
            width: 620px;
            height: 620px;
            border: 80px solid rgba(254, 202, 202, 0.28);
            border-radius: 999px;
        }

        .login-logo {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }

        .login-logo-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            color: #dc2626;
        }

        .login-logo-icon svg {
            width: 38px;
            height: 38px;
        }

        .login-logo strong,
        .login-logo span {
            display: block;
            font-size: 18px;
            font-weight: 1000;
            line-height: 1.05;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .login-logo strong {
            color: #0f172a;
        }

        .login-logo span {
            color: #dc2626;
        }

        .login-visual-content {
            position: relative;
            z-index: 1;
            max-width: 760px;
            margin-top: 150px;
        }

        .login-visual-content > p,
        .login-heading p {
            margin: 0 0 16px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 1000;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .login-visual-content h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(54px, 7vw, 88px);
            line-height: 0.98;
            letter-spacing: -0.075em;
        }

        .login-visual-content > span {
            display: block;
            max-width: 720px;
            margin-top: 28px;
            color: #64748b;
            font-size: 17px;
            line-height: 1.8;
        }

        .login-feature-list {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 34px;
        }

        .login-feature-list article {
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            border-radius: 999px;
            color: #334155;
            background: rgba(255, 255, 255, 0.86);
            font-size: 13px;
            font-weight: 900;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
        }

        .login-feature-list span {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            color: #dc2626;
            background: #fee2e2;
        }

        .login-feature-list svg {
            width: 18px;
            height: 18px;
        }

        .login-panel {
            display: grid;
            place-items: center;
            padding: 44px;
        }

        .login-card {
            width: min(100%, 520px);
        }

        .login-heading h2 {
            margin: 0;
            color: #0f172a;
            font-size: 40px;
            line-height: 1.1;
            letter-spacing: -0.06em;
        }

        .login-heading span {
            display: block;
            margin-top: 12px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .login-alert {
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 900;
            line-height: 1.6;
        }

        .login-alert.is-success {
            border: 1px solid #bbf7d0;
            color: #166534;
            background: #f0fdf4;
        }

        .login-alert.is-danger {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fff1f2;
        }

        .login-alert-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .login-alert-actions a {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            border-radius: 11px;
            color: #ffffff;
            background: #dc2626;
            font-size: 12px;
            font-weight: 900;
            text-decoration: none;
        }

        .login-google-button {
            min-height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            color: #0f172a;
            background: #ffffff;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
            transition: 160ms ease;
        }

        .login-google-button:hover {
            transform: translateY(-1px);
            border-color: #fecaca;
            box-shadow: 0 18px 42px rgba(220, 38, 38, 0.12);
        }

        .login-google-button span {
            width: 26px;
            height: 26px;
            display: grid;
            place-items: center;
        }

        .login-google-button svg {
            width: 24px;
            height: 24px;
        }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
        }

        .login-divider span {
            height: 1px;
            flex: 1;
            background: #e2e8f0;
        }

        .login-divider p {
            margin: 0;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 900;
        }

        .login-form {
            display: grid;
            gap: 16px;
        }

        .login-field {
            display: grid;
            gap: 8px;
        }

        .login-field > span {
            color: #334155;
            font-size: 12px;
            font-weight: 1000;
        }

        .login-field div {
            min-height: 56px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            transition: 160ms ease;
        }

        .login-field div:focus-within {
            border-color: #f87171;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.42);
        }

        .login-field svg {
            width: 20px;
            height: 20px;
            color: #94a3b8;
            flex: 0 0 auto;
        }

        .login-field input {
            width: 100%;
            border: 0;
            outline: none;
            color: #0f172a;
            background: transparent;
            font: inherit;
            font-size: 14px;
        }

        .login-field small {
            color: #dc2626;
            font-size: 11px;
            font-weight: 800;
        }

        .login-form-extra {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .login-form-extra a {
            color: #dc2626;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            white-space: nowrap;
        }

        .login-form-extra a:hover {
            color: #b91c1c;
        }

        .login-remember {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
        }

        .login-remember input {
            width: 18px;
            height: 18px;
            accent-color: #dc2626;
        }

        .login-submit {
            min-height: 58px;
            border: 0;
            border-radius: 16px;
            color: #ffffff;
            background: #dc2626;
            font: inherit;
            font-size: 14px;
            font-weight: 1000;
            cursor: pointer;
            box-shadow: 0 18px 38px rgba(220, 38, 38, 0.2);
        }

        .login-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .login-register-box {
            display: grid;
            gap: 12px;
            margin-top: 24px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
        }

        .login-register-box strong {
            display: block;
            color: #0f172a;
            font-size: 14px;
            font-weight: 1000;
            text-align: center;
        }

        .login-register-box a {
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: #ffffff;
            background: #dc2626;
            font-size: 13px;
            font-weight: 1000;
            text-decoration: none;
        }

        .login-register-box a.is-outline {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
        }

        .login-note {
            margin: 22px 0 0;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.6;
            text-align: center;
        }

        @media (max-width: 1100px) {
            .login-page {
                grid-template-columns: 1fr;
            }

            .login-visual {
                min-height: 520px;
            }

            .login-visual-content {
                margin-top: 90px;
            }
        }

        @media (max-width: 680px) {
            .login-visual,
            .login-panel {
                padding: 32px 22px;
            }

            .login-visual-content h1 {
                font-size: 48px;
            }

            .login-heading h2 {
                font-size: 34px;
            }

            .login-feature-list {
                flex-direction: column;
                align-items: stretch;
            }

            .login-feature-list article {
                border-radius: 18px;
            }
        }
    </style>
</div>