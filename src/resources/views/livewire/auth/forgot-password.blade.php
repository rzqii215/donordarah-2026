<div>
    <header class="auth-heading">
        <p class="auth-kicker">Pemulihan akun</p>
        <h2>Lupa password?</h2>
        <p>Masukkan email akun Anda. Kami akan mengirimkan tautan untuk membuat password baru.</p>
    </header>

    @if ($berhasilDikirim)
        <section class="auth-success-panel" role="status">
            <span class="auth-success-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 6h16v12H4z"/>
                    <path d="m4 8 8 5 8-5"/>
                    <path d="m9.5 17 1.7 1.7L15 15"/>
                </svg>
            </span>
            <h3>Periksa email Anda</h3>
            <p>
                Jika email terdaftar, tautan reset password telah dikirim.
                Periksa folder Spam atau Promosi jika belum terlihat.
            </p>
            <a href="{{ route('login') }}" class="auth-button auth-button--primary auth-button--full">
                Kembali ke halaman masuk
            </a>
        </section>
    @else
        <div class="auth-alert auth-alert--warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 11v5M12 8h.01"/>
            </svg>
            <span>Demi keamanan, sistem tidak akan memberitahukan apakah suatu email terdaftar.</span>
        </div>

        <form wire:submit="sendResetLink" class="auth-form" novalidate>
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
                        placeholder="nama@email.com"
                        autocomplete="email"
                        inputmode="email"
                        autofocus
                        required
                    >
                </span>
                @error('email') <span class="auth-field-error">{{ $message }}</span> @enderror
            </label>

            <button
                type="submit"
                class="auth-button auth-button--primary auth-button--full"
                wire:loading.attr="disabled"
                wire:target="sendResetLink"
            >
                <span wire:loading.remove wire:target="sendResetLink">Kirim tautan reset</span>
                <span wire:loading wire:target="sendResetLink">Mengirim tautan...</span>
            </button>
        </form>

        <div class="auth-divider">sudah ingat password?</div>

        <a href="{{ route('login') }}" class="auth-button auth-button--secondary auth-button--full">
            Kembali ke halaman masuk
        </a>
    @endif

    <p class="auth-privacy-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="5" y="10" width="14" height="11" rx="2"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
        </svg>
        Tautan reset memiliki masa berlaku terbatas.
    </p>
</div>