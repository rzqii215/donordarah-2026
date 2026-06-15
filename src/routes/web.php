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

    if ($pengguna->hasRole('hospital')) {
        if (Route::has('rumah-sakit.beranda')) {
            return redirect()->route(
                'rumah-sakit.beranda'
            );
        }

        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'error',
                'Portal Rumah Sakit belum tersedia.'
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
require __DIR__ . '/donor.php';