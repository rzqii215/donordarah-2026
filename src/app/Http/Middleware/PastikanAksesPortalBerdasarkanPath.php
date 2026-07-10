<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanAksesPortalBerdasarkanPath
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $path = trim($request->path(), '/');

        if ($this->adalahPathPendonor($path)) {
            $redirect = $this->pastikanAksesPendonor();

            if ($redirect instanceof Response) {
                return $redirect;
            }
        }

        if ($this->adalahPathPemohonDonor($path)) {
            $redirect = $this->pastikanAksesPemohonDonor();

            if ($redirect instanceof Response) {
                return $redirect;
            }
        }

        return $next($request);
    }

    private function pastikanAksesPendonor(): ?Response
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect('/login');
        }

        if ($this->userAdalahPendonor($user)) {
            return null;
        }

        return redirect(
            $this->tujuanRedirectUser($user)
        );
    }

    private function pastikanAksesPemohonDonor(): ?Response
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect('/login');
        }

        if ($this->userAdalahPemohonDonor($user)) {
            return null;
        }

        return redirect(
            $this->tujuanRedirectUser($user)
        );
    }

    private function adalahPathPendonor(
        string $path
    ): bool {
        return $path === 'donor'
            || str_starts_with($path, 'donor/');
    }

    private function adalahPathPemohonDonor(
        string $path
    ): bool {
        return $path === 'pemohon-donor'
            || str_starts_with($path, 'pemohon-donor/');
    }

    private function userAdalahPendonor(
        mixed $user
    ): bool {
        if ($this->punyaRelasiProfil($user, 'profilPendonor')) {
            return true;
        }

        return $this->punyaSalahSatuRole($user, [
            'donor',
            'pendonor',
        ]);
    }

    private function userAdalahPemohonDonor(
        mixed $user
    ): bool {
        if ($this->punyaRelasiProfil($user, 'profilRumahSakit')) {
            return true;
        }

        return $this->punyaSalahSatuRole($user, [
            'pemohon_donor',
            'pemohon-donor',
            'pemohon donor',
            'pemohon',
            'rumah_sakit',
            'rumah sakit',
            'hospital',
        ]);
    }

    private function userAdalahAdmin(
        mixed $user
    ): bool {
        return $this->punyaSalahSatuRole($user, [
            'super_admin',
            'admin',
            'administrator',
            'petugas',
        ]);
    }

    private function tujuanRedirectUser(
        mixed $user
    ): string {
        if ($this->userAdalahPendonor($user)) {
            return '/donor';
        }

        if ($this->userAdalahPemohonDonor($user)) {
            return '/pemohon-donor';
        }

        if ($this->userAdalahAdmin($user)) {
            return '/admin';
        }

        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return '/login';
    }

    private function punyaRelasiProfil(
        mixed $user,
        string $relation
    ): bool {
        if (! method_exists($user, $relation)) {
            return false;
        }

        return $user->{$relation}()->exists();
    }

    /**
     * @param array<int, string> $roles
     */
    private function punyaSalahSatuRole(
        mixed $user,
        array $roles
    ): bool {
        if (! method_exists($user, 'hasRole')) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}