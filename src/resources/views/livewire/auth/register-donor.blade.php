<div
    @if ($metodePendaftaran === '')
        wire:init="pilihManual"
    @endif
>
    @php
        $googleRouteAda = \Illuminate\Support\Facades\Route::has(
            'google.redirect'
        );

        $googleUrl = $googleRouteAda
            ? route('google.redirect', [
                'tujuan' => 'donor',
            ])
            : '#';
    @endphp

    <header class="auth-heading auth-heading-row">
        <div class="auth-heading-copy">
            <p class="auth-kicker">
                Akun pendonor
            </p>

            <h2>
                Daftar sebagai pendonor
            </h2>

            <p>
                Lengkapi data diri agar jadwal dan riwayat donor
                dapat dikelola dari satu akun.
            </p>
        </div>

        <a
            href="{{ route('login') }}"
            class="auth-back-link"
        >
            Sudah punya akun?
        </a>
    </header>

    @error('metodePendaftaran')
        <div
            class="auth-alert auth-alert--danger"
            role="alert"
        >
            <span>
                {{ $message }}
            </span>
        </div>
    @enderror

    @if ($this->menggunakanGoogle())
        <div class="auth-method-active">
            <span>
                Akun Google berhasil terhubung.
                Lengkapi data pendonor untuk menyelesaikan pendaftaran.
            </span>

            <button
                type="button"
                wire:click="resetMetodePendaftaran"
            >
                Ganti metode
            </button>
        </div>
    @endif

    <form
        wire:submit="register"
        class="auth-form"
        novalidate
    >
        <section class="auth-section">
            <div class="auth-section-title">
                <h3>
                    Informasi akun
                </h3>

                <p>
                    Data ini digunakan untuk masuk dan menerima
                    informasi layanan.
                </p>
            </div>

            <div class="auth-grid">
                <label class="auth-field">
                    <span class="auth-field-label">
                        Nama lengkap
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('name')
                            auth-control--error
                        @enderror"
                    >
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Nama sesuai identitas"
                            autocomplete="name"
                            @readonly(
                                $this->menggunakanGoogle()
                            )
                        >
                    </span>

                    @error('name')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Nomor HP
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('nomor_telepon')
                            auth-control--error
                        @enderror"
                    >
                        <input
                            type="tel"
                            wire:model="nomor_telepon"
                            placeholder="08xxxxxxxxxx"
                            autocomplete="tel"
                            inputmode="tel"
                        >
                    </span>

                    @error('nomor_telepon')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field auth-span-full">
                    <span class="auth-field-label">
                        Alamat email
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('email')
                            auth-control--error
                        @enderror"
                    >
                        <input
                            type="email"
                            wire:model="email"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            inputmode="email"
                            @readonly(
                                $this->menggunakanGoogle()
                            )
                        >
                    </span>

                    @error('email')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                @unless ($this->menggunakanGoogle())
                    <label
                        class="auth-field"
                        x-data="{
                            tampil: false
                        }"
                    >
                        <span class="auth-field-label">
                            Password
                            <span class="auth-required">*</span>
                        </span>

                        <span
                            class="auth-control
                            @error('password')
                                auth-control--error
                            @enderror"
                        >
                            <input
                                x-bind:type="
                                    tampil
                                        ? 'text'
                                        : 'password'
                                "
                                wire:model="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                            >

                            <button
                                type="button"
                                class="auth-password-toggle"
                                x-on:click="
                                    tampil = ! tampil
                                "
                                aria-label="Tampilkan password"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M2.5 12s3.5-6 9.5-6
                                        9.5 6 9.5 6-3.5 6-9.5 6
                                        -9.5-6-9.5-6Z"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="2.5"
                                    />
                                </svg>
                            </button>
                        </span>

                        @error('password')
                            <span class="auth-field-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </label>

                    <label
                        class="auth-field"
                        x-data="{
                            tampil: false
                        }"
                    >
                        <span class="auth-field-label">
                            Konfirmasi password
                            <span class="auth-required">*</span>
                        </span>

                        <span class="auth-control">
                            <input
                                x-bind:type="
                                    tampil
                                        ? 'text'
                                        : 'password'
                                "
                                wire:model="password_confirmation"
                                placeholder="Ulangi password"
                                autocomplete="new-password"
                            >

                            <button
                                type="button"
                                class="auth-password-toggle"
                                x-on:click="
                                    tampil = ! tampil
                                "
                                aria-label="Tampilkan konfirmasi password"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M2.5 12s3.5-6 9.5-6
                                        9.5 6 9.5 6-3.5 6-9.5 6
                                        -9.5-6-9.5-6Z"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="2.5"
                                    />
                                </svg>
                            </button>
                        </span>
                    </label>
                @endunless
            </div>
        </section>

        <section class="auth-section">
            <div class="auth-section-title">
                <h3>
                    Profil pendonor
                </h3>

                <p>
                    Informasi dasar untuk menyesuaikan proses
                    pendaftaran donor.
                </p>
            </div>

            <div class="auth-grid auth-grid--three">
                <label class="auth-field">
                    <span class="auth-field-label">
                        Tanggal lahir
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('tanggal_lahir')
                            auth-control--error
                        @enderror"
                    >
                        <input
                            type="date"
                            wire:model="tanggal_lahir"
                            autocomplete="bday"
                        >
                    </span>

                    @error('tanggal_lahir')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Jenis kelamin
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('jenis_kelamin')
                            auth-control--error
                        @enderror"
                    >
                        <select wire:model="jenis_kelamin">
                            <option value="">
                                Pilih
                            </option>

                            @foreach (
                                $this->opsiJenisKelamin()
                                as $opsi
                            )
                                <option
                                    value="{{ $opsi['value'] }}"
                                >
                                    {{ $opsi['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </span>

                    @error('jenis_kelamin')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Golongan darah
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('golongan_darah')
                            auth-control--error
                        @enderror"
                    >
                        <select wire:model="golongan_darah">
                            <option value="">
                                Pilih
                            </option>

                            @foreach (
                                $this->opsiGolonganDarah()
                                as $opsi
                            )
                                <option
                                    value="{{ $opsi['value'] }}"
                                >
                                    {{ $opsi['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </span>

                    @error('golongan_darah')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Rhesus
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('rhesus')
                            auth-control--error
                        @enderror"
                    >
                        <select wire:model="rhesus">
                            <option value="">
                                Pilih
                            </option>

                            @foreach (
                                $this->opsiRhesusDarah()
                                as $opsi
                            )
                                <option
                                    value="{{ $opsi['value'] }}"
                                >
                                    {{ $opsi['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </span>

                    @error('rhesus')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>
            </div>
        </section>

        <section class="auth-section">
            <div class="auth-section-title">
                <h3>
                    Alamat domisili
                </h3>

                <p>
                    Digunakan untuk membantu menampilkan lokasi
                    donor yang relevan.
                </p>
            </div>

            <div class="auth-grid">
                <label class="auth-field auth-span-full">
                    <span class="auth-field-label">
                        Alamat lengkap
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('alamat')
                            auth-control--error
                        @enderror"
                    >
                        <textarea
                            wire:model="alamat"
                            placeholder="Nama jalan, nomor rumah, RT/RW"
                        ></textarea>
                    </span>

                    @error('alamat')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Provinsi
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('provinsi')
                            auth-control--error
                        @enderror"
                    >
                        <input
                            type="text"
                            wire:model="provinsi"
                            placeholder="Provinsi"
                        >
                    </span>

                    @error('provinsi')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Kota/Kabupaten
                        <span class="auth-required">*</span>
                    </span>

                    <span
                        class="auth-control
                        @error('kota')
                            auth-control--error
                        @enderror"
                    >
                        <input
                            type="text"
                            wire:model="kota"
                            placeholder="Kota atau kabupaten"
                        >
                    </span>

                    @error('kota')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Kecamatan
                    </span>

                    <span class="auth-control">
                        <input
                            type="text"
                            wire:model="kecamatan"
                            placeholder="Kecamatan"
                        >
                    </span>

                    @error('kecamatan')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label class="auth-field">
                    <span class="auth-field-label">
                        Kode pos
                    </span>

                    <span class="auth-control">
                        <input
                            type="text"
                            wire:model="kode_pos"
                            placeholder="Contoh: 12190"
                            inputmode="numeric"
                        >
                    </span>

                    @error('kode_pos')
                        <span class="auth-field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </label>
            </div>
        </section>

        <label class="auth-check">
            <input
                type="checkbox"
                wire:model="bersedia_dihubungi"
            >

            <span>
                Saya bersedia menerima informasi jadwal dan
                kegiatan donor melalui kontak yang saya daftarkan.
            </span>
        </label>

        <div class="auth-form-actions">
            <button
                type="submit"
                class="
                    auth-button
                    auth-button--primary
                    auth-button--full
                "
                wire:loading.attr="disabled"
                wire:target="register"
            >
                <span
                    wire:loading.remove
                    wire:target="register"
                >
                    Buat akun pendonor
                </span>

                <span
                    wire:loading
                    wire:target="register"
                >
                    Mendaftarkan...
                </span>
            </button>
        </div>
    </form>

    @unless ($this->menggunakanGoogle())
        <section aria-label="Pendaftaran pendonor dengan Google">
            <div class="auth-divider">
                <span>
                    atau daftar lebih cepat
                </span>
            </div>

            <a
                href="{{ $googleUrl }}"
                class="
                    auth-button
                    auth-button--secondary
                    auth-button--full
                "
                @if (! $googleRouteAda)
                    aria-disabled="true"
                    tabindex="-1"
                @endif
            >
                <svg
                    class="auth-google-icon"
                    viewBox="0 0 48 48"
                    aria-hidden="true"
                >
                    <path
                        fill="#FFC107"
                        d="M43.6 20.1H42V20H24v8h11.3
                        C33.7 32.7 29.2 36 24 36
                        c-6.6 0-12-5.4-12-12s5.4-12
                        12-12c3.1 0 5.8 1.2 8 3
                        l5.6-5.6C34 6.1 29.3 4 24 4
                        13 4 4 13 4 24s9 20 20 20
                        20-9 20-20c0-1.3-.1-2.6-.4-3.9Z"
                    />

                    <path
                        fill="#FF3D00"
                        d="m6.3 14.7 6.6 4.8
                        C14.7 15.1 19 12 24 12
                        c3.1 0 5.8 1.2 8 3
                        l5.6-5.6C34 6.1 29.3 4 24 4
                        c-7.7 0-14.3 4.3-17.7 10.7Z"
                    />

                    <path
                        fill="#4CAF50"
                        d="M24 44c5.2 0 9.9-2 13.4-5.2
                        l-6.2-5.2C29.2 35.1 26.7 36 24 36
                        c-5.2 0-9.6-3.3-11.3-7.9
                        l-6.5 5C9.5 39.6 16.2 44 24 44Z"
                    />

                    <path
                        fill="#1976D2"
                        d="M43.6 20.1H42V20H24v8h11.3
                        a12 12 0 0 1-4.1 5.6l6.2 5.2
                        C37 39.2 44 34 44 24
                        c0-1.3-.1-2.6-.4-3.9Z"
                    />
                </svg>

                <span>
                    Daftar pendonor dengan Google
                </span>
            </a>

            <p class="auth-privacy-note">
                Nama dan alamat email dari Google akan digunakan
                untuk mempercepat pengisian akun.
            </p>
        </section>
    @endunless
</div>