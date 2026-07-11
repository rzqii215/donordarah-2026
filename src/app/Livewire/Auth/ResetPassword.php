<?php

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
