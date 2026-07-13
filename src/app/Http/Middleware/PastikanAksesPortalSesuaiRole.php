<?php

namespace App\Http\Middleware;

use App\Enums\StatusPengguna;
use App\Models\User;
use BackedEnum;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanAksesPortalSesuaiRole
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = Auth::guard('web')->user();

        if (! $user instanceof User) {
            if (
                $this->portalDiminta($request)
                !== null
            ) {
                return redirect()
                    ->guest('/login');
            }

            return $next($request);
        }

        /*
         * Link verifikasi bertanda tangan tetap boleh
         * diproses meskipun status akun berubah.
         */
        if (
            $request->routeIs(
                'verification.verify'
            )
        ) {
            return $next($request);
        }

        if (! $this->statusPenggunaAktif($user)) {
            return $this->keluarkanDanRedirect(
                $request,
                $this->pesanStatusPengguna($user)
            );
        }

        if (
            ! $user->hasVerifiedEmail()
            && ! $this->halamanDiizinkanSebelumVerifikasi(
                $request
            )
        ) {
            return redirect()
                ->route('verification.notice');
        }

        $halamanUtama =
            $this->halamanUtamaBerdasarkanRole(
                $user
            );

        if ($halamanUtama === '/login') {
            return $this->keluarkanDanRedirect(
                $request,
                'Akun tidak memiliki akses ke portal.'
            );
        }

        if ($this->halamanAdmin($request)) {
            if (! $this->adalahAdmin($user)) {
                return redirect()
                    ->to($halamanUtama)
                    ->with(
                        'error',
                        'Anda tidak memiliki akses ke panel admin.'
                    );
            }

            return $next($request);
        }

        $portalDiminta = $this->portalDiminta(
            $request
        );

        if ($portalDiminta !== null) {
            if (
                $portalDiminta === 'donor'
                && ! $this->adalahPendonor($user)
            ) {
                return redirect()
                    ->to($halamanUtama)
                    ->with(
                        'error',
                        'Anda tidak memiliki akses ke portal pendonor.'
                    );
            }

            if (
                $portalDiminta === 'pemohon-donor'
                && ! $this->adalahPemohonDonor(
                    $user
                )
            ) {
                return redirect()
                    ->to($halamanUtama)
                    ->with(
                        'error',
                        'Anda tidak memiliki akses ke portal pemohon donor.'
                    );
            }

            if (
                $this->adalahAdmin($user)
            ) {
                return redirect()
                    ->to('/admin')
                    ->with(
                        'error',
                        'Akun admin diarahkan ke panel admin.'
                    );
            }

            return $next($request);
        }

        if (
            $this->halamanAuthPublik($request)
        ) {
            return redirect()
                ->to($halamanUtama);
        }

        return $next($request);
    }

    private function halamanAdmin(
        Request $request
    ): bool {
        return $request->is('admin')
            || $request->is('admin/*');
    }

    private function halamanAuthPublik(
        Request $request
    ): bool {
        return $request->is('login')
            || $request->is('login/*')
            || $request->is('register')
            || $request->is('register/*');

    }

    private function halamanDiizinkanSebelumVerifikasi(
        Request $request
    ): bool {
        return $request->routeIs([
            'verification.notice',
            'verification.verify',
            'verification.send',
            'logout',
            'donor.logout',
            'pemohon-donor.logout',
        ]);
    }

    private function portalDiminta(
        Request $request
    ): ?string {
        if (
            $request->is('donor')
            || $request->is('donor/*')
        ) {
            return 'donor';
        }

        if (
            $request->is('pemohon-donor')
            || $request->is('pemohon-donor/*')
        ) {
            return 'pemohon-donor';
        }

        return null;
    }

    private function halamanUtamaBerdasarkanRole(
        User $user
    ): string {
        if ($this->adalahAdmin($user)) {
            return '/admin';
        }

        if ($this->adalahPemohonDonor($user)) {
            return '/pemohon-donor';
        }

        if ($this->adalahPendonor($user)) {
            return '/donor';
        }

        return '/login';
    }

    private function adalahAdmin(
        User $user
    ): bool {
        return $this->roles($user)
            ->contains(
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
            );
    }

    private function adalahPendonor(
        User $user
    ): bool {
        return $this->roles($user)
            ->contains(
                fn (string $role): bool => in_array(
                    $role,
                    [
                        'donor',
                        'pendonor',
                    ],
                    true
                )
            );
    }

    private function adalahPemohonDonor(
        User $user
    ): bool {
        return $this->roles($user)
            ->contains(
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
            );
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

    /**
     * @return Collection<int, string>
     */
    private function roles(
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

    private function keluarkanDanRedirect(
        Request $request,
        string $message
    ): RedirectResponse {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'error',
                $message
            );
    }
}
