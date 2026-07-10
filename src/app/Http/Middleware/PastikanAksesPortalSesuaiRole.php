<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PastikanAksesPortalSesuaiRole
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::guard('web')->user();

        if ($this->halamanAdmin($request)) {
            if ($user === null) {
                return $next($request);
            }

            if (! $this->adalahAdmin($user)) {
                return redirect()
                    ->to($this->halamanUtamaBerdasarkanRole($user))
                    ->with('error', 'Anda tidak memiliki akses ke panel admin.');
            }

            return $next($request);
        }

        $portalDiminta = $this->portalDiminta($request);

        if ($portalDiminta !== null) {
            if ($user === null) {
                return redirect()->guest('/login');
            }

            $halamanUtama = $this->halamanUtamaBerdasarkanRole($user);

            if ($halamanUtama === '/admin') {
                return redirect()
                    ->to('/admin')
                    ->with('error', 'Akun admin diarahkan ke panel admin.');
            }

            if (
                $portalDiminta === 'donor'
                && ! $this->adalahPendonor($user)
            ) {
                return redirect()
                    ->to($halamanUtama)
                    ->with('error', 'Anda tidak memiliki akses ke portal pendonor.');
            }

            if (
                $portalDiminta === 'pemohon-donor'
                && ! $this->adalahPemohonDonor($user)
            ) {
                return redirect()
                    ->to($halamanUtama)
                    ->with('error', 'Anda tidak memiliki akses ke portal pemohon donor.');
            }

            return $next($request);
        }

        if (
            $this->halamanAuthPublik($request)
            && $user !== null
        ) {
            $halamanUtama = $this->halamanUtamaBerdasarkanRole($user);

            if ($halamanUtama !== '/login') {
                return redirect()->to($halamanUtama);
            }
        }

        return $next($request);
    }

    private function halamanAdmin(Request $request): bool
    {
        return $request->is('admin')
            || $request->is('admin/*');
    }

    private function halamanAuthPublik(Request $request): bool
    {
        return $request->is('login')
            || $request->is('register')
            || $request->is('register/*');
    }

    private function portalDiminta(Request $request): ?string
    {
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

    private function halamanUtamaBerdasarkanRole(mixed $user): string
    {
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

    private function adalahAdmin(mixed $user): bool
    {
        $roles = $this->roles($user);

        return $roles->contains(
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

    private function adalahPendonor(mixed $user): bool
    {
        $roles = $this->roles($user);

        return $roles->contains(
            fn (string $role): bool =>
                $role === 'donor'
                || $role === 'pendonor'
                || str_contains($role, 'pendonor')
        );
    }

    private function adalahPemohonDonor(mixed $user): bool
    {
        $roles = $this->roles($user);

        return $roles->contains(
            fn (string $role): bool =>
                $role === 'rumah_sakit'
                || $role === 'rumah-sakit'
                || $role === 'pemohon_donor'
                || $role === 'pemohon-donor'
                || $role === 'pemohon'
                || str_contains($role, 'pemohon')
                || str_contains($role, 'rumah')
                || str_contains($role, 'sakit')
                || str_contains($role, 'hospital')
        );
    }

    private function roles(mixed $user): Collection
    {
        if (
            ! is_object($user)
            || ! method_exists($user, 'getRoleNames')
        ) {
            return collect();
        }

        return $user->getRoleNames()
            ->map(fn (string $role): string => strtolower(trim($role)))
            ->filter()
            ->values();
    }
}
