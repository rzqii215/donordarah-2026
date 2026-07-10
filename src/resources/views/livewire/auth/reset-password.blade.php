<div class="reset-password-page">
    <section class="reset-password-card">
        <div class="reset-password-brand">
            <div class="reset-password-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.4"
                >
                    <rect x="5" y="11" width="14" height="10" rx="2" />
                    <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                </svg>
            </div>

            <div>
                <strong>Donor Darah</strong>
                <span>Password Baru</span>
            </div>
        </div>

        <div class="reset-password-heading">
            <p>Reset Password</p>

            <h1>
                Buat password baru
            </h1>

            <span>
                Masukkan password baru untuk akun Anda. Gunakan minimal 8 karakter.
            </span>
        </div>

        <form wire:submit="resetPassword" class="reset-password-form">
            <label class="reset-password-field">
                <span>Alamat Email</span>

                <input
                    type="email"
                    wire:model="email"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    readonly
                >

                @error('email')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <label class="reset-password-field">
                <span>Password Baru</span>

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

            <label class="reset-password-field">
                <span>Konfirmasi Password Baru</span>

                <input
                    type="password"
                    wire:model="password_confirmation"
                    placeholder="Ulangi password baru"
                    autocomplete="new-password"
                >
            </label>

            <button
                type="submit"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    Simpan Password Baru
                </span>

                <span wire:loading>
                    Memproses...
                </span>
            </button>
        </form>

        <div class="reset-password-footer">
            <a href="{{ url('/login') }}">
                Kembali ke Login
            </a>
        </div>
    </section>

    <style>
        .reset-password-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            background:
                radial-gradient(circle at 80% 20%, rgba(220, 38, 38, 0.12), transparent 28rem),
                linear-gradient(135deg, #fff1f2 0%, #ffffff 65%);
        }

        .reset-password-card {
            width: min(100%, 520px);
            padding: 34px;
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
        }

        .reset-password-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 34px;
        }

        .reset-password-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            color: #dc2626;
            background: #fee2e2;
        }

        .reset-password-icon svg {
            width: 28px;
            height: 28px;
        }

        .reset-password-brand strong {
            display: block;
            color: #0f172a;
            font-size: 16px;
            font-weight: 1000;
            line-height: 1.1;
        }

        .reset-password-brand span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
        }

        .reset-password-heading p {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 1000;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .reset-password-heading h1 {
            margin: 0;
            color: #0f172a;
            font-size: 38px;
            line-height: 1.05;
            letter-spacing: -0.06em;
        }

        .reset-password-heading span {
            display: block;
            margin-top: 14px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.75;
        }

        .reset-password-form {
            display: grid;
            gap: 18px;
            margin-top: 26px;
        }

        .reset-password-field {
            display: grid;
            gap: 8px;
        }

        .reset-password-field span {
            color: #334155;
            font-size: 12px;
            font-weight: 1000;
        }

        .reset-password-field input {
            width: 100%;
            min-height: 56px;
            padding: 0 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            font: inherit;
            font-size: 14px;
        }

        .reset-password-field input:read-only {
            color: #64748b;
            background: #f8fafc;
            cursor: not-allowed;
        }

        .reset-password-field input:focus {
            border-color: #f87171;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.44);
        }

        .reset-password-field small {
            color: #dc2626;
            font-size: 11px;
            font-weight: 800;
        }

        .reset-password-form button {
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

        .reset-password-form button:hover {
            background: #b91c1c;
        }

        .reset-password-form button:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .reset-password-footer {
            margin-top: 24px;
            text-align: center;
        }

        .reset-password-footer a {
            color: #dc2626;
            font-size: 13px;
            font-weight: 1000;
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .reset-password-card {
                padding: 26px;
                border-radius: 24px;
            }

            .reset-password-heading h1 {
                font-size: 32px;
            }
        }
    </style>
</div>