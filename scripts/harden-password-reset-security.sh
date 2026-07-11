#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== 1. Backup file lama =="

mkdir -p scripts/backups-password-reset-security

cp src/app/Livewire/Auth/ForgotPassword.php scripts/backups-password-reset-security/ForgotPassword.php.bak 2>/dev/null || true
cp src/app/Livewire/Auth/ResetPassword.php scripts/backups-password-reset-security/ResetPassword.php.bak 2>/dev/null || true

echo "[OK] Backup selesai."

echo ""
echo "== 2. Overwrite ForgotPassword.php dengan versi aman =="

python3 - <<'PY'
from pathlib import Path

path = Path("src/app/Livewire/Auth/ForgotPassword.php")

path.write_text(r"""<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;
use Throwable;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 900;

    public string $email = '';

    public bool $berhasilDikirim = false;

    public function sendResetLink(): void
    {
        $this->resetErrorBag();

        $this->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.max' => 'Alamat email terlalu panjang.',
        ]);

        $throttleKey = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError(
                'email',
                'Terlalu banyak permintaan reset password. Silakan coba lagi dalam ' . $seconds . ' detik.'
            );

            return;
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        try {
            $status = Password::sendResetLink([
                'email' => $this->normalizedEmail(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $this->addError(
                'email',
                'Permintaan reset password belum dapat diproses. Silakan coba beberapa saat lagi.'
            );

            return;
        }

        if (
            $status === Password::RESET_LINK_SENT ||
            $status === Password::INVALID_USER
        ) {
            $this->tampilkanPesanBerhasil();

            return;
        }

        report(
            new RuntimeException(
                'Password reset link failed with status: ' . $status
            )
        );

        $this->addError(
            'email',
            'Permintaan reset password belum dapat diproses. Silakan coba beberapa saat lagi.'
        );
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }

    private function normalizedEmail(): string
    {
        return Str::lower(
            trim($this->email)
        );
    }

    private function throttleKey(): string
    {
        return 'forgot-password:' . sha1(
            $this->normalizedEmail() . '|' . request()->ip()
        );
    }

    private function tampilkanPesanBerhasil(): void
    {
        $this->berhasilDikirim = true;

        session()->flash(
            'success',
            'Jika email tersebut terdaftar, link reset password akan dikirim ke email Anda.'
        );
    }
}
""")

print("ForgotPassword.php berhasil diperbarui.")
PY

echo ""
echo "== 3. Overwrite ResetPassword.php dengan versi aman =="

python3 - <<'PY'
from pathlib import Path

path = Path("src/app/Livewire/Auth/ResetPassword.php")

path.write_text(r"""<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.auth')]
class ResetPassword extends Component
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 900;

    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(): mixed
    {
        $this->resetErrorBag();

        $this->validate([
            'token' => [
                'required',
                'string',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.max' => 'Alamat email terlalu panjang.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
        ]);

        $throttleKey = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError(
                'email',
                'Terlalu banyak percobaan reset password. Silakan coba lagi dalam ' . $seconds . ' detik.'
            );

            return null;
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        try {
            $status = Password::reset([
                'email' => $this->normalizedEmail(),
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ], function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);

                $user->save();

                event(
                    new PasswordReset($user)
                );
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->addError(
                'email',
                'Reset password belum dapat diproses. Silakan coba beberapa saat lagi.'
            );

            return null;
        }

        if ($status === Password::PASSWORD_RESET) {
            RateLimiter::clear($throttleKey);

            return redirect()
                ->to('/login')
                ->with(
                    'success',
                    'Password berhasil diubah. Silakan masuk menggunakan password baru.'
                );
        }

        $this->addError(
            'email',
            'Link reset password tidak valid atau sudah kedaluwarsa.'
        );

        return null;
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }

    private function normalizedEmail(): string
    {
        return Str::lower(
            trim($this->email)
        );
    }

    private function throttleKey(): string
    {
        return 'reset-password:' . sha1(
            $this->normalizedEmail() . '|' . $this->token . '|' . request()->ip()
        );
    }
}
""")

print("ResetPassword.php berhasil diperbarui.")
PY

echo ""
echo "== 4. Update teks tampilan forgot password agar generic =="

python3 - <<'PY'
from pathlib import Path

path = Path("src/resources/views/livewire/auth/forgot-password.blade.php")

if not path.exists():
    print("Skip, forgot-password.blade.php tidak ditemukan.")
    raise SystemExit

content = path.read_text()

content = content.replace(
    "Masukkan email yang terdaftar. Kami akan mengirimkan link reset password melalui email.",
    "Masukkan alamat email Anda. Jika email tersebut terdaftar, kami akan mengirimkan link reset password."
)

content = content.replace(
    "Link reset password berhasil dikirim. Silakan cek email Anda.",
    "Jika email tersebut terdaftar, link reset password akan dikirim ke email Anda."
)

path.write_text(content)

print("Teks forgot password berhasil dibuat generic.")
PY

echo ""
echo "== 5. Cek syntax PHP =="

docker compose exec php php -l app/Livewire/Auth/ForgotPassword.php
docker compose exec php php -l app/Livewire/Auth/ResetPassword.php

echo ""
echo "== 6. Hapus cache Laravel =="

docker compose exec php sh -lc "rm -f bootstrap/cache/*.php"
docker compose exec php sh -lc "rm -f storage/framework/views/*.php"

docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

echo ""
echo "== 7. Cek route password reset =="

docker compose exec php php artisan route:list --name=password

echo ""
echo "== 8. Cek config mail =="

docker compose exec php php artisan tinker --execute='dump([
    "mail_default" => config("mail.default"),
    "resend_key_ada" => filled(config("services.resend.key")),
    "from_address" => config("mail.from.address"),
    "from_name" => config("mail.from.name"),
    "app_url" => config("app.url"),
]);'

echo ""
echo "Hardening password reset selesai."
