<div class="forgot-password-page">
    <section class="forgot-password-card">
        <div class="forgot-password-brand">
            <div class="forgot-password-icon">
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
                <strong>Donor Darah</strong>
                <span>Reset Password</span>
            </div>
        </div>

        <div class="forgot-password-heading">
            <p>Lupa Kata Sandi</p>

            <h1>
                Reset password akun Anda
            </h1>

            <span>
                Masukkan email yang terdaftar. Kami akan mengirimkan link reset password melalui email.
            </span>
        </div>

        @if (session('success'))
            <div class="forgot-password-alert is-success">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit="sendResetLink" class="forgot-password-form">
            <label class="forgot-password-field">
                <span>Alamat Email</span>

                <input
                    type="email"
                    wire:model="email"
                    placeholder="nama@email.com"
                    autocomplete="email"
                >

                @error('email')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <button
                type="submit"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    Kirim Link Reset Password
                </span>

                <span wire:loading>
                    Mengirim...
                </span>
            </button>
        </form>

        <div class="forgot-password-footer">
            <a href="{{ url('/login') }}">
                Kembali ke Login
            </a>
        </div>
    </section>

    <style>
        .forgot-password-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            background:
                radial-gradient(circle at 80% 20%, rgba(220, 38, 38, 0.12), transparent 28rem),
                linear-gradient(135deg, #fff1f2 0%, #ffffff 65%);
        }

        .forgot-password-card {
            width: min(100%, 520px);
            padding: 34px;
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
        }

        .forgot-password-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 34px;
        }

        .forgot-password-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            color: #dc2626;
            background: #fee2e2;
        }

        .forgot-password-icon svg {
            width: 28px;
            height: 28px;
        }

        .forgot-password-brand strong {
            display: block;
            color: #0f172a;
            font-size: 16px;
            font-weight: 1000;
            line-height: 1.1;
        }

        .forgot-password-brand span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
        }

        .forgot-password-heading p {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 1000;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .forgot-password-heading h1 {
            margin: 0;
            color: #0f172a;
            font-size: 38px;
            line-height: 1.05;
            letter-spacing: -0.06em;
        }

        .forgot-password-heading span {
            display: block;
            margin-top: 14px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.75;
        }

        .forgot-password-alert {
            margin-top: 22px;
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 900;
            line-height: 1.6;
        }

        .forgot-password-alert.is-success {
            border: 1px solid #bbf7d0;
            color: #166534;
            background: #f0fdf4;
        }

        .forgot-password-form {
            display: grid;
            gap: 18px;
            margin-top: 26px;
        }

        .forgot-password-field {
            display: grid;
            gap: 8px;
        }

        .forgot-password-field span {
            color: #334155;
            font-size: 12px;
            font-weight: 1000;
        }

        .forgot-password-field input {
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

        .forgot-password-field input:focus {
            border-color: #f87171;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.44);
        }

        .forgot-password-field small {
            color: #dc2626;
            font-size: 11px;
            font-weight: 800;
        }

        .forgot-password-form button {
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

        .forgot-password-form button:hover {
            background: #b91c1c;
        }

        .forgot-password-form button:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .forgot-password-footer {
            margin-top: 24px;
            text-align: center;
        }

        .forgot-password-footer a {
            color: #dc2626;
            font-size: 13px;
            font-weight: 1000;
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .forgot-password-card {
                padding: 26px;
                border-radius: 24px;
            }

            .forgot-password-heading h1 {
                font-size: 32px;
            }
        }
    </style>
</div>