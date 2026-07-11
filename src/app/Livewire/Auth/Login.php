<?php

namespace App\Livewire\Auth;

use App\Enums\StatusPengguna;
use App\Models\User;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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
            'email' => mb_strtolower(
                trim($this->email)
            ),

            'password' => $this->password,
        ], $this->remember);

        if (! $berhasil) {
            RateLimiter::hit(
                $this->throttleKey()
            );

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            $this->keluarkanPengguna();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Sesi login tidak valid. Silakan masuk kembali.'
                );
        }

        RateLimiter::clear(
            $this->throttleKey()
        );

        if (! $this->statusPenggunaAktif($user)) {
            $pesan = $this->pesanStatusPengguna(
                $user
            );

            $this->keluarkanPengguna();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    $pesan
                );
        }

        /*
         * Menggunakan session helper agar session regeneration
         * tetap bekerja pada request HTTP dan pengujian Livewire.
         */
        session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice');
        }

        $redirectPath = $this->redirectPathByRole(
            $user
        );

        if ($redirectPath === '/login') {
            $this->keluarkanPengguna();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Akun tidak memiliki akses ke portal.'
                );
        }

        return redirect()
            ->intended(
                $redirectPath
            );
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }

    private function pastikanTidakTerlaluBanyakPercobaan(): void
    {
        if (
            ! RateLimiter::tooManyAttempts(
                $this->throttleKey(),
                5
            )
        ) {
            return;
        }

        $detik = RateLimiter::availableIn(
            $this->throttleKey()
        );

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$detik} detik.",
        ]);
    }

    private function throttleKey(): string
    {
        return Str::lower(
            trim($this->email)
        ) . '|' . request()->ip();
    }

    private function statusPenggunaAktif(
        User $user
    ): bool {
        return $this->nilaiStatusPengguna($user)
            === StatusPengguna::Aktif->value;
    }

    private function pesanStatusPengguna(
        User $user
    ): string {
        return match (
            $this->nilaiStatusPengguna($user)
        ) {
            StatusPengguna::Menunggu->value => 'Akun masih menunggu aktivasi.',

            StatusPengguna::TidakAktif->value => 'Akun sedang tidak aktif. Hubungi administrator.',

            StatusPengguna::Ditangguhkan->value => 'Akun sedang ditangguhkan. Hubungi administrator.',

            StatusPengguna::Ditolak->value => 'Akun tidak dapat digunakan karena pengajuan akun ditolak.',

            default => 'Akun belum dapat digunakan. Hubungi administrator.',
        };
    }

    private function nilaiStatusPengguna(
        User $user
    ): string {
        $status = $user->status;

        if ($status instanceof BackedEnum) {
            return strtolower(
                trim(
                    (string) $status->value
                )
            );
        }

        return strtolower(
            trim(
                (string) $status
            )
        );
    }

    private function keluarkanPengguna(): void
    {
        Auth::guard('web')->logout();

        /*
         * session() mengembalikan store yang sama dengan guard web,
         * tetapi tidak bergantung pada session yang terpasang di Request.
         */
        session()->invalidate();
        session()->regenerateToken();
    }

    private function redirectPathByRole(
        User $user
    ): string {
        $roles = $this->rolesPengguna(
            $user
        );

        if (
            $roles->contains(
                fn (string $role): bool => in_array(
                    $role,
                    [
                        'super_admin',
                        'super-admin',
                        'admin',
                        'petugas',
                    ],
                    true
                )
            )
        ) {
            return '/admin';
        }

        if (
            $roles->contains(
                fn (string $role): bool => in_array(
                    $role,
                    [
                        'pemohon_donor',
                        'pemohon-donor',
                        'rumah_sakit',
                        'rumah-sakit',
                    ],
                    true
                )
            )
        ) {
            return '/pemohon-donor';
        }

        if (
            $roles->contains(
                fn (string $role): bool => in_array(
                    $role,
                    [
                        'donor',
                        'pendonor',
                    ],
                    true
                )
            )
        ) {
            return '/donor';
        }

        return '/login';
    }

    /**
     * @return Collection<int, string>
     */
    private function rolesPengguna(
        User $user
    ): Collection {
        return $user->getRoleNames()
            ->map(
                fn (string $role): string => strtolower(
                    trim($role)
                )
            )
            ->filter()
            ->values();
    }
}
