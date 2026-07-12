<div>
    <header class="auth-heading">
        <p class="auth-kicker">Buat password baru</p>
        <h2>Atur ulang password</h2>
        <p>Gunakan minimal 8 karakter dan hindari password yang pernah Anda gunakan sebelumnya.</p>
    </header>

    <form wire:submit="resetPassword" class="auth-form" novalidate>
        <label class="auth-field">
            <span class="auth-field-label">Alamat email</span>
            <span class="auth-control @error('email') auth-control--error @enderror">
                <span class="auth-control-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="m3 7 9 6 9-6"/>
                    </svg>
                </span>
                <input
                    type="email"
                    wire:model="email"
                    autocomplete="email"
                    inputmode="email"
                    required
                >
            </span>
            @error('email') <span class="auth-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="auth-field" x-data="{ tampil: false }">
            <span class="auth-field-label">Password baru</span>
            <span class="auth-control @error('password') auth-control--error @enderror">
                <span class="auth-control-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="5" y="10" width="14" height="11" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>
                </span>
                <input
                    x-bind:type="tampil ? 'text' : 'password'"
                    wire:model="password"
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                    required
                >
                <button type="button" class="auth-password-toggle" x-on:click="tampil = ! tampil" aria-label="Tampilkan atau sembunyikan password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                        <circle cx="12" cy="12" r="2.5"/>
                    </svg>
                </button>
            </span>
            @error('password') <span class="auth-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="auth-field" x-data="{ tampil: false }">
            <span class="auth-field-label">Konfirmasi password baru</span>
            <span class="auth-control">
                <span class="auth-control-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="m8 12 2.5 2.5L16 9"/>
                        <rect x="4" y="4" width="16" height="16" rx="4"/>
                    </svg>
                </span>
                <input
                    x-bind:type="tampil ? 'text' : 'password'"
                    wire:model="password_confirmation"
                    placeholder="Ulangi password baru"
                    autocomplete="new-password"
                    required
                >
                <button type="button" class="auth-password-toggle" x-on:click="tampil = ! tampil" aria-label="Tampilkan atau sembunyikan konfirmasi password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                        <circle cx="12" cy="12" r="2.5"/>
                    </svg>
                </button>
            </span>
        </label>

        <button
            type="submit"
            class="auth-button auth-button--primary auth-button--full"
            wire:loading.attr="disabled"
            wire:target="resetPassword"
        >
            <span wire:loading.remove wire:target="resetPassword">Simpan password baru</span>
            <span wire:loading wire:target="resetPassword">Menyimpan...</span>
        </button>
    </form>

    <div class="auth-divider">batalkan proses</div>

    <a href="{{ route('login') }}" class="auth-button auth-button--secondary auth-button--full">
        Kembali ke halaman masuk
    </a>
</div>