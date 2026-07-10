<div class="register-donor-page">
    @php
        $googleRouteAda = \Illuminate\Support\Facades\Route::has('google.redirect');

        $googleUrl = $googleRouteAda
            ? route('google.redirect', ['tujuan' => 'donor'])
            : '#';
    @endphp

    <section class="register-donor-hero">
        <p>Portal Pendonor</p>

        <h1>
            Daftar sebagai Pendonor Darah
        </h1>

        <span>
            Pilih metode pendaftaran terlebih dahulu. Anda bisa daftar manual
            atau menggunakan akun Google, lalu tetap melengkapi data pendonor.
        </span>
    </section>

    @if (session('success'))
        <div class="register-donor-alert is-success">
            {{ session('success') }}
        </div>
    @endif

    @error('metodePendaftaran')
        <div class="register-donor-alert is-danger">
            {{ $message }}
        </div>
    @enderror

    @if ($metodePendaftaran === '')
        <section class="register-donor-choice">
            <article>
                <div class="register-donor-choice-icon is-google">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.651 32.657 29.223 36 24 36c-6.627 0-12-5.373-12-12S17.373 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917Z"/>
                        <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691Z"/>
                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.197l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.617-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44Z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917Z"/>
                    </svg>
                </div>

                <h2>
                    Daftar dengan Google
                </h2>

                <p>
                    Nama dan email diambil dari akun Google. Setelah itu Anda
                    tetap wajib melengkapi nomor HP, golongan darah, rhesus,
                    dan alamat.
                </p>

                <a href="{{ $googleUrl }}">
                    Lanjutkan dengan Google
                </a>
            </article>

            <article>
                <div class="register-donor-choice-icon is-manual">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M4 6h16" />
                        <path d="M4 12h16" />
                        <path d="M4 18h10" />
                    </svg>
                </div>

                <h2>
                    Isi Form Manual
                </h2>

                <p>
                    Gunakan email dan kata sandi baru. Semua data akun dan data
                    pendonor diisi langsung melalui form pendaftaran.
                </p>

                <button
                    type="button"
                    wire:click="pilihManual"
                >
                    Isi Form Manual
                </button>
            </article>
        </section>

        <div class="register-donor-footer-link">
            Sudah punya akun?
            <a href="{{ url('/login') }}">Masuk</a>
        </div>
    @else
        <form wire:submit="register" class="register-donor-form">
                @include('components.auth.google-auth-actions', [
                    'mode' => 'donor',
                ])

            <section class="register-donor-method-active">
                <div>
                    <span>
                        Metode Pendaftaran
                    </span>

                    <strong>
                        {{ $metodePendaftaran === 'google' ? 'Daftar dengan Google' : 'Isi Form Manual' }}
                    </strong>

                    <p>
                        {{ $metodePendaftaran === 'google'
                            ? 'Akun Google tersambung. Lengkapi data pendonor di bawah ini.'
                            : 'Lengkapi data akun dan data pendonor di bawah ini.' }}
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="resetMetodePendaftaran"
                >
                    Ganti Metode
                </button>
            </section>

            @if ($this->menggunakanGoogle())
                <section class="register-donor-google-connected">
                    <strong>
                        Akun Google berhasil tersambung.
                    </strong>

                    <p>
                        Email dari Google akan digunakan sebagai akun masuk.
                        Kata sandi tidak wajib diisi untuk metode ini.
                    </p>
                </section>
            @endif

            <section class="register-donor-section">
                <div class="register-donor-section-heading">
                    <h2>
                        Informasi Akun
                    </h2>

                    <p>
                        Data ini digunakan untuk masuk ke portal pendonor.
                    </p>
                </div>

                <div class="register-donor-grid">
                    <label class="register-donor-field">
                        <span>Nama Lengkap</span>

                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Nama lengkap"
                            autocomplete="name"
                            @readonly($this->menggunakanGoogle())
                        >

                        @error('name')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Alamat Email</span>

                        <input
                            type="email"
                            wire:model="email"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            @readonly($this->menggunakanGoogle())
                        >

                        @error('email')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Nomor HP</span>

                        <input
                            type="text"
                            wire:model="nomor_telepon"
                            placeholder="08xxxxxxxxxx"
                            autocomplete="tel"
                        >

                        @error('nomor_telepon')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Tanggal Lahir</span>

                        <input
                            type="date"
                            wire:model="tanggal_lahir"
                        >

                        @error('tanggal_lahir')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    @if (! $this->menggunakanGoogle())
                        <label class="register-donor-field">
                            <span>Kata Sandi</span>

                            <input
                                type="password"
                                wire:model="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                            >

                            @error('password')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label class="register-donor-field">
                            <span>Konfirmasi Kata Sandi</span>

                            <input
                                type="password"
                                wire:model="password_confirmation"
                                placeholder="Ulangi kata sandi"
                                autocomplete="new-password"
                            >
                        </label>
                    @endif
                </div>
            </section>

            <section class="register-donor-section">
                <div class="register-donor-section-heading">
                    <h2>
                        Data Pendonor
                    </h2>

                    <p>
                        Data ini membantu petugas menyesuaikan proses donor.
                    </p>
                </div>

                <div class="register-donor-grid">
                    <label class="register-donor-field">
                        <span>Jenis Kelamin</span>

                        <select wire:model="jenis_kelamin">
                            <option value="">Pilih jenis kelamin</option>

                            @foreach ($this->opsiJenisKelamin() as $opsi)
                                <option value="{{ $opsi['value'] }}">
                                    {{ $opsi['label'] }}
                                </option>
                            @endforeach
                        </select>

                        @error('jenis_kelamin')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Golongan Darah</span>

                        <select wire:model="golongan_darah">
                            <option value="">Pilih golongan darah</option>

                            @foreach ($this->opsiGolonganDarah() as $opsi)
                                <option value="{{ $opsi['value'] }}">
                                    {{ $opsi['label'] }}
                                </option>
                            @endforeach
                        </select>

                        @error('golongan_darah')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Rhesus</span>

                        <select wire:model="rhesus">
                            <option value="">Pilih rhesus</option>

                            @foreach ($this->opsiRhesusDarah() as $opsi)
                                <option value="{{ $opsi['value'] }}">
                                    {{ $opsi['label'] }}
                                </option>
                            @endforeach
                        </select>

                        @error('rhesus')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Provinsi</span>

                        <input
                            type="text"
                            wire:model="provinsi"
                            placeholder="Contoh: DKI Jakarta"
                        >

                        @error('provinsi')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Kota/Kabupaten</span>

                        <input
                            type="text"
                            wire:model="kota"
                            placeholder="Contoh: Jakarta Selatan"
                        >

                        @error('kota')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Kecamatan</span>

                        <input
                            type="text"
                            wire:model="kecamatan"
                            placeholder="Opsional"
                        >

                        @error('kecamatan')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field">
                        <span>Kode Pos</span>

                        <input
                            type="text"
                            wire:model="kode_pos"
                            placeholder="Opsional"
                        >

                        @error('kode_pos')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="register-donor-field register-donor-field-full">
                        <span>Alamat Lengkap</span>

                        <textarea
                            wire:model="alamat"
                            rows="4"
                            placeholder="Tulis alamat lengkap domisili Anda"
                        ></textarea>

                        @error('alamat')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>
                </div>
            </section>

            <label class="register-donor-check">
                <input
                    type="checkbox"
                    wire:model="bersedia_dihubungi"
                >

                <span>
                    Saya bersedia dihubungi terkait informasi jadwal donor dan pelayanan donor darah.
                </span>
            </label>

            <div class="register-donor-actions">
                <a href="{{ url('/login') }}">
                    Sudah punya akun? Masuk
                </a>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>
                        Daftar sebagai Pendonor
                    </span>

                    <span wire:loading>
                        Memproses...
                    </span>
                </button>
            </div>
        </form>
    @endif

    <style>
        .register-donor-page {
            width: min(100%, 1120px);
            margin: 0 auto;
            padding: 44px 24px 70px;
        }

        .register-donor-hero {
            padding: 38px 0 34px;
            border-bottom: 1px solid #fee2e2;
        }

        .register-donor-hero p {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .register-donor-hero h1 {
            max-width: 760px;
            margin: 0;
            color: #0f172a;
            font-size: clamp(42px, 7vw, 72px);
            line-height: 0.98;
            letter-spacing: -0.07em;
        }

        .register-donor-hero span {
            display: block;
            max-width: 760px;
            margin-top: 20px;
            color: #64748b;
            font-size: 15px;
            line-height: 1.8;
        }

        .register-donor-alert {
            margin-top: 24px;
            padding: 15px 17px;
            border-radius: 18px;
            font-size: 13px;
            font-weight: 900;
        }

        .register-donor-alert.is-success {
            border: 1px solid #bbf7d0;
            color: #166534;
            background: #f0fdf4;
        }

        .register-donor-alert.is-danger {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fff1f2;
        }

        .register-donor-choice {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
            margin-top: 34px;
        }

        .register-donor-choice article,
        .register-donor-section,
        .register-donor-method-active,
        .register-donor-google-connected {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.06);
        }

        .register-donor-choice article {
            display: grid;
            align-content: start;
            gap: 16px;
            padding: 26px;
            border-radius: 26px;
        }

        .register-donor-choice-icon {
            width: 62px;
            height: 62px;
            display: grid;
            place-items: center;
            border-radius: 22px;
        }

        .register-donor-choice-icon.is-google {
            background: #f8fafc;
        }

        .register-donor-choice-icon.is-manual {
            color: #dc2626;
            background: #fee2e2;
        }

        .register-donor-choice-icon svg {
            width: 30px;
            height: 30px;
        }

        .register-donor-choice h2 {
            margin: 0;
            color: #0f172a;
            font-size: 25px;
            line-height: 1.2;
            letter-spacing: -0.04em;
        }

        .register-donor-choice p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.75;
        }

        .register-donor-choice a,
        .register-donor-choice button,
        .register-donor-actions button,
        .register-donor-actions a,
        .register-donor-method-active button {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }

        .register-donor-choice a,
        .register-donor-choice button,
        .register-donor-actions button {
            border: 0;
            color: #ffffff;
            background: #dc2626;
            box-shadow: 0 16px 32px rgba(220, 38, 38, 0.18);
        }

        .register-donor-footer-link {
            margin-top: 24px;
            color: #64748b;
            font-size: 14px;
            font-weight: 800;
        }

        .register-donor-footer-link a {
            color: #dc2626;
            font-weight: 900;
            text-decoration: none;
        }

        .register-donor-form {
            display: grid;
            gap: 24px;
            margin-top: 34px;
        }

        .register-donor-method-active {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 22px;
            border-radius: 24px;
        }

        .register-donor-method-active span {
            color: #dc2626;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .register-donor-method-active strong {
            display: block;
            margin-top: 5px;
            color: #0f172a;
            font-size: 20px;
            letter-spacing: -0.03em;
        }

        .register-donor-method-active p {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .register-donor-method-active button {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
            box-shadow: none;
            white-space: nowrap;
        }

        .register-donor-google-connected {
            padding: 18px;
            border-color: #bbf7d0;
            border-radius: 20px;
            background: #f0fdf4;
        }

        .register-donor-google-connected strong {
            color: #14532d;
            font-size: 14px;
            font-weight: 900;
        }

        .register-donor-google-connected p {
            margin: 6px 0 0;
            color: #166534;
            font-size: 13px;
            line-height: 1.7;
            font-weight: 700;
        }

        .register-donor-section {
            display: grid;
            gap: 22px;
            padding: 24px;
            border-radius: 24px;
        }

        .register-donor-section-heading h2 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            letter-spacing: -0.04em;
        }

        .register-donor-section-heading p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .register-donor-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .register-donor-field {
            display: grid;
            gap: 8px;
        }

        .register-donor-field-full {
            grid-column: 1 / -1;
        }

        .register-donor-field span {
            color: #334155;
            font-size: 12px;
            font-weight: 900;
        }

        .register-donor-field input,
        .register-donor-field select,
        .register-donor-field textarea {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            font: inherit;
            font-size: 14px;
            transition: 160ms ease;
        }

        .register-donor-field input,
        .register-donor-field select {
            min-height: 54px;
            padding: 0 16px;
        }

        .register-donor-field textarea {
            resize: vertical;
            padding: 15px 16px;
        }

        .register-donor-field input:read-only {
            color: #64748b;
            background: #f8fafc;
        }

        .register-donor-field input:focus,
        .register-donor-field select:focus,
        .register-donor-field textarea:focus {
            border-color: #f87171;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.42);
        }

        .register-donor-field small {
            color: #dc2626;
            font-size: 11px;
            font-weight: 800;
        }

        .register-donor-check {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 18px;
            border: 1px solid #fee2e2;
            border-radius: 20px;
            background: #fff7f7;
            color: #334155;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.7;
        }

        .register-donor-check input {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            accent-color: #dc2626;
        }

        .register-donor-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .register-donor-actions a {
            color: #dc2626;
            background: transparent;
            box-shadow: none;
        }

        .register-donor-actions button {
            min-width: 220px;
        }

        .register-donor-actions button:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        @media (max-width: 860px) {
            .register-donor-choice,
            .register-donor-grid {
                grid-template-columns: 1fr;
            }

            .register-donor-field-full {
                grid-column: auto;
            }

            .register-donor-method-active,
            .register-donor-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .register-donor-actions button,
            .register-donor-actions a,
            .register-donor-method-active button {
                width: 100%;
            }
        }
    </style>
</div>