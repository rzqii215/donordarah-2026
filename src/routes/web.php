<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $pengguna = Auth::user();

    if (! $pengguna instanceof User) {
        return redirect()->route('login');
    }

    if ($pengguna->hasRole('donor')) {
        return redirect()->route('donor.beranda');
    }

    if (
        $pengguna->hasAnyRole([
            'super_admin',
            'petugas',
        ])
    ) {
        return redirect('/admin');
    }

    if ($pengguna->hasRole('pemohon_donor')) {
        if (Route::has('pemohon-donor.beranda')) {
            return redirect()->route(
                'pemohon-donor.beranda'
            );
        }

        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'error',
                'Portal Pemohon Donor belum tersedia.'
            );
    }

    Auth::guard('web')->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->with(
            'error',
            'Akun tidak memiliki akses ke portal.'
        );
})->name('home');

require __DIR__ . '/auth.php';
require __DIR__ . '/email-verification.php';

Route::middleware([
    'auth',
    'verified',
])->group(function (): void {
    require __DIR__ . '/donor.php';

    if (file_exists(__DIR__ . '/pemohon-donor.php')) {
        require __DIR__ . '/pemohon-donor.php';
        require __DIR__ . '/pemohon-donor-riwayat-bukti.php';
    }
});

require __DIR__ . '/auth-public.php';
require __DIR__ . '/google-auth.php';
require __DIR__ . '/logout-public.php';
require __DIR__ . '/password-reset.php';