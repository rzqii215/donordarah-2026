<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate(): mixed
    {
        $this->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $this->pastikanTidakTerlaluBanyakPercobaan();

        $berhasil = Auth::attempt([
            'email' => mb_strtolower(trim($this->email)),
            'password' => $this->password,
        ], $this->remember);

        if (! $berhasil) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        request()->session()->regenerate();

        $user = Auth::user();

        return redirect()
            ->intended(
                $this->redirectPathByRole($user)
            );
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }

    private function pastikanTidakTerlaluBanyakPercobaan(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $detik = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$detik} detik.",
        ]);
    }

    private function throttleKey(): string
    {
        return Str::lower(trim($this->email)) . '|' . request()->ip();
    }

    private function redirectPathByRole(mixed $user): string
    {
        if (! $user instanceof User) {
            return '/login';
        }

        $roles = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()
                ->map(fn (string $role): string => strtolower($role))
            : collect();

        if (
            $roles->contains('super_admin')
            || $roles->contains('super-admin')
            || $roles->contains('admin')
            || $roles->contains('petugas')
        ) {
            return '/admin';
        }

        $punyaRolePemohon = $roles->contains(
            fn (string $role): bool =>
                str_contains($role, 'pemohon')
                || str_contains($role, 'rumah')
                || str_contains($role, 'sakit')
                || str_contains($role, 'hospital')
        );

        if ($punyaRolePemohon) {
            return '/pemohon-donor';
        }

        $punyaRolePendonor = $roles->contains(
            fn (string $role): bool =>
                $role === 'donor'
                || $role === 'pendonor'
                || str_contains($role, 'pendonor')
        );

        if ($punyaRolePendonor) {
            return '/donor';
        }

        return '/login';
    }
}