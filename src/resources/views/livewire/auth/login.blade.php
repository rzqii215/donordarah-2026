<div>
    @php
        $googleRouteAda = \Illuminate\Support\Facades\Route::has(
            'google.redirect'
        );

        $googleUrl = $googleRouteAda
            ? route('google.redirect', [
                'tujuan' => 'login',
            ])
            : '#';
    @endphp

    <header class="auth-heading">
        <p class="auth-kicker">
            Selamat datang kembali
        </p>

        <h2>
            Masuk ke akun Anda
        </h2>

        <p>
            Kelola aktivitas donor dan pengajuan darah melalui akun
            yang telah terdaftar.
        </p>
    </header>

    @if (session('success'))
        <div
            class="auth-alert auth-alert--success"
            role="status"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <circle cx="12" cy="12" r="9" />
                <path d="m8 12 2.5 2.5L16 9" />
            </svg>

            <span>
                {{ session('success') }}
            </span>
        </div>
    @endif

    @if ($errors->has('email'))
        <div
            class="auth-alert auth-alert--danger"
            role="alert"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <circle cx="12" cy="12" r="9" />
                <path d="M12 7v6M12 17h.01" />
            </svg>

            <span>
                {{ $errors->first('email') }}
            </span>
        </div>
    @endif

    <form
        wire:submit="authenticate"
        class="auth-form"
        novalidate
    >
        <label class="auth-field">
            <span class="auth-field-label">
                Alamat email
            </span>

            <span
                class="auth-control
                    @error('email')
                        auth-control--error
                    @enderror"
            >
                <span class="auth-control-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect
                            x="3"
                            y="5"
                            width="18"
                            height="14"
                            rx="2"
                        />

                        <path d="m3 7 9 6 9-6" />
                    </svg>
                </span>

                <input
                    type="email"
                    wire:model="email"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    inputmode="email"
                    required
                >
            </span>

            @error('email')
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
                Password
            </span>

            <span
                class="auth-control
                    @error('password')
                        auth-control--error
                    @enderror"
            >
                <span class="auth-control-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect
                            x="5"
                            y="10"
                            width="14"
                            height="11"
                            rx="2"
                        />

                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                    </svg>
                </span>

                <input
                    x-bind:type="
                        tampil
                            ? 'text'
                            : 'password'
                    "
                    wire:model="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="auth-password-toggle"
                    x-on:click="tampil = ! tampil"
                    x-bind:aria-label="
                        tampil
                            ? 'Sembunyikan password'
                            : 'Tampilkan password'
                    "
                >
                    <svg
                        x-show="! tampil"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                        />

                        <circle
                            cx="12"
                            cy="12"
                            r="2.5"
                        />
                    </svg>

                    <svg
                        x-cloak
                        x-show="tampil"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            d="m3 3 18 18M10.6 6.2c.5-.1.9-.2 1.4-.2 6 0 9.5 6 9.5 6a16 16 0 0 1-2.2 2.8M6.2 6.2A15.6 15.6 0 0 0 2.5 12s3.5 6 9.5 6c1.6 0 3-.4 4.2-1"
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

        <div class="auth-options">
            <label class="auth-check">
                <input
                    type="checkbox"
                    wire:model="remember"
                >

                <span>
                    Ingat saya di perangkat ini
                </span>
            </label>

            <a
                href="{{ url('/forgot-password') }}"
                class="auth-text-link"
            >
                Lupa password?
            </a>
        </div>

        <button
            type="submit"
            class="auth-button auth-button--primary auth-button--full"
            wire:loading.attr="disabled"
            wire:target="authenticate"
        >
            <span
                wire:loading.remove
                wire:target="authenticate"
            >
                Masuk
            </span>

            <span
                wire:loading
                wire:target="authenticate"
            >
                Memeriksa akun...
            </span>
        </button>
    </form>

    <div class="auth-divider">
        atau lanjutkan dengan
    </div>

    @if ($googleRouteAda)
        <a
            href="{{ $googleUrl }}"
            class="auth-button auth-button--secondary auth-button--full"
        >
            <svg
                class="auth-google-icon"
                viewBox="0 0 48 48"
                aria-hidden="true"
            >
                <path
                    fill="#FFC107"
                    d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3l5.6-5.6C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9Z"
                />

                <path
                    fill="#FF3D00"
                    d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 8 3l5.6-5.6C34 6.1 29.3 4 24 4c-7.7 0-14.3 4.3-17.7 10.7Z"
                />

                <path
                    fill="#4CAF50"
                    d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44Z"
                />

                <path
                    fill="#1976D2"
                    d="M43.6 20.1H42V20H24v8h11.3a12 12 0 0 1-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9Z"
                />
            </svg>

            Masuk dengan Google
        </a>
    @else
        <button
            type="button"
            class="auth-button auth-button--secondary auth-button--full"
            disabled
        >
            <svg
                class="auth-google-icon"
                viewBox="0 0 48 48"
                aria-hidden="true"
            >
                <path
                    fill="#FFC107"
                    d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3l5.6-5.6C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9Z"
                />

                <path
                    fill="#FF3D00"
                    d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 8 3l5.6-5.6C34 6.1 29.3 4 24 4c-7.7 0-14.3 4.3-17.7 10.7Z"
                />

                <path
                    fill="#4CAF50"
                    d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44Z"
                />

                <path
                    fill="#1976D2"
                    d="M43.6 20.1H42V20H24v8h11.3a12 12 0 0 1-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9Z"
                />
            </svg>

            Login Google belum tersedia
        </button>
    @endif

    <section class="auth-register-links">
        <p>
            Belum memiliki akun?
        </p>

        <div class="auth-register-actions">
            <a
                href="{{ url('/register/donor') }}"
                class="auth-button auth-button--primary"
            >
                Daftar Pendonor
            </a>

            <a
                href="{{ url('/register/pemohon-donor') }}"
                class="auth-button auth-button--secondary"
            >
                Daftar Pemohon
            </a>
        </div>
    </section>

    <p class="auth-privacy-note">
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path
                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
            />
        </svg>

        Data akun hanya digunakan untuk pelayanan Donor Darah.
    </p>
</div>