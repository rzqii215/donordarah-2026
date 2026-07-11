<?php

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
