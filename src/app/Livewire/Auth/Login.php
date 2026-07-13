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
    public string $portal = 'umum';

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(
        string $portal = 'umum'
    ): void {
        $this->portal = in_array(
            $portal,
            [
                'umum',
                'donor',
                'pemohon',
            ],
            true
        )
            ? $portal
            : 'umum';
    }

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
                ->route(
                    $this->namaRouteLogin()
                )
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
                ->route(
                    $this->namaRouteLogin()
                )
                ->with(
                    'error',
                    $pesan
                );
        }

        $redirectPath = $this->redirectPathByRole(
            $user
        );

        if (
            ! $this->penggunaSesuaiPortal(
                $redirectPath
            )
        ) {
            $pesan = match ($this->portal) {
                'pemohon' => 'Akun tersebut bukan akun pemohon donor. Gunakan halaman masuk pendonor jika Anda terdaftar sebagai pendonor.',

                'donor' => 'Akun tersebut bukan akun pendonor. Gunakan halaman masuk pemohon jika Anda terdaftar sebagai pemohon donor.',

                default => 'Akun tersebut tidak memiliki akses ke portal pengguna.',
            };

            $this->keluarkanPengguna();

            return redirect()
                ->route(
                    $this->namaRouteLogin()
                )
                ->with(
                    'error',
                    $pesan
                );
        }

        session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice');
        }

        return redirect()->to(
            $redirectPath
        );
    }

    public function render(): View
    {
        return view(
            'livewire.auth.login',
            [
                'konfigurasiPortal' => $this->konfigurasiPortal(),
            ]
        );
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
        return implode('|', [
            $this->portal,

            Str::lower(
                trim($this->email)
            ),

            (string) request()->ip(),
        ]);
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

        session()->invalidate();
        session()->regenerateToken();
    }

    private function penggunaSesuaiPortal(
        string $redirectPath
    ): bool {
        return match ($this->portal) {
            'donor' => $redirectPath === '/donor',

            'pemohon' => $redirectPath === '/pemohon-donor',

            /*
             * Mode umum digunakan oleh /login dan
             * pengujian autentikasi lama.
             */
            default => in_array(
                $redirectPath,
                [
                    '/donor',
                    '/pemohon-donor',
                    '/admin',
                ],
                true
            ),
        };
    }

    private function namaRouteLogin(): string
    {
        return match ($this->portal) {
            'donor' => 'login.donor',

            'pemohon' => 'login.pemohon',

            default => 'login',
        };
    }

    /**
     * @return array<string, string>
     */
    private function konfigurasiPortal(): array
    {
        if ($this->portal === 'pemohon') {
            return [
                'kicker' => 'Portal Pemohon Donor',

                'judul' => 'Masuk sebagai pemohon',

                'deskripsi' => 'Kelola pengajuan kebutuhan darah, distribusi, dan profil instansi melalui akun pemohon donor.',

                'teks_tombol' => 'Masuk ke Portal Pemohon',

                'register_url' => route(
                    'register.pemohon-donor'
                ),

                'register_label' => 'Daftar sebagai Pemohon',

                'portal_lain_url' => route('login.donor'),

                'portal_lain_label' => 'Masuk sebagai Pendonor',
            ];
        }

        if ($this->portal === 'donor') {
            return [
                'kicker' => 'Portal Pendonor',

                'judul' => 'Masuk sebagai pendonor',

                'deskripsi' => 'Kelola jadwal, pendaftaran, profil, dan riwayat donor melalui akun pendonor.',

                'teks_tombol' => 'Masuk ke Portal Pendonor',

                'register_url' => route('register.donor'),

                'register_label' => 'Daftar sebagai Pendonor',

                'portal_lain_url' => route('login.pemohon'),

                'portal_lain_label' => 'Masuk sebagai Pemohon Donor',
            ];
        }

        return [
            'kicker' => 'Portal Donor Darah',

            'judul' => 'Masuk ke akun Anda',

            'deskripsi' => 'Masuk menggunakan akun yang telah terdaftar untuk mengakses layanan Donor Darah.',

            'teks_tombol' => 'Masuk',

            'register_url' => route('register.index'),

            'register_label' => 'Buat Akun Baru',

            'portal_lain_url' => route('login.pemohon'),

            'portal_lain_label' => 'Masuk khusus sebagai Pemohon Donor',
        ];
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
