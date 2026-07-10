@php
    $mode = $mode ?? 'login';

    $googleUrl = match ($mode) {
        'donor' => route('google.redirect', ['tujuan' => 'donor']),
        'pemohon-donor' => route('google.redirect', ['tujuan' => 'pemohon-donor']),
        default => route('google.redirect', ['tujuan' => 'login']),
    };

    $buttonText = match ($mode) {
        'donor' => 'Daftar dengan Google sebagai Pendonor',
        'pemohon-donor' => 'Daftar dengan Google sebagai Pemohon Donor',
        default => 'Masuk dengan Google',
    };
@endphp

<div class="auth-google-wrapper">
    @if ($mode !== 'login' && session()->has('google_register'))
        <div class="auth-google-connected">
            <strong>Akun Google tersambung.</strong>
            <span>Silakan lengkapi data pendaftaran di bawah ini.</span>
        </div>
    @endif

    <a href="{{ $googleUrl }}" class="auth-google-button">
        <span class="auth-google-icon">
            <svg viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.651 32.657 29.223 36 24 36c-6.627 0-12-5.373-12-12S17.373 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917Z"/>
                <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691Z"/>
                <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.197l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.617-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44Z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917Z"/>
            </svg>
        </span>

        <span>{{ $buttonText }}</span>
    </a>

    <div class="auth-google-divider">
        <span></span>
        <p>atau gunakan email dan kata sandi</p>
        <span></span>
    </div>
</div>

<style>
    .auth-google-wrapper {
        display: grid;
        gap: 14px;
        margin: 18px 0;
    }

    .auth-google-button {
        min-height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
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

    .auth-google-button:hover {
        transform: translateY(-1px);
        border-color: #fecaca;
        box-shadow: 0 18px 42px rgba(220, 38, 38, 0.12);
    }

    .auth-google-icon {
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
    }

    .auth-google-icon svg {
        width: 24px;
        height: 24px;
    }

    .auth-google-divider {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .auth-google-divider span {
        height: 1px;
        flex: 1;
        background: #e2e8f0;
    }

    .auth-google-divider p {
        margin: 0;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .auth-google-connected {
        padding: 14px 16px;
        border: 1px solid #bbf7d0;
        border-radius: 16px;
        background: #f0fdf4;
    }

    .auth-google-connected strong {
        display: block;
        color: #14532d;
        font-size: 13px;
        font-weight: 900;
    }

    .auth-google-connected span {
        display: block;
        margin-top: 4px;
        color: #166534;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.6;
    }
</style>
