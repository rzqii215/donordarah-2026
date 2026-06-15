<?php

use App\Livewire\Donor\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth',
])
    ->prefix('donor')
    ->name('donor.')
    ->group(function (): void {
        Route::get('/', function () {
            return redirect()->route(
                'donor.beranda'
            );
        })->name('index');

        Route::get('/beranda', Portal::class)
            ->defaults('section', 'beranda')
            ->name('beranda');

        Route::get('/jadwal-donor', Portal::class)
            ->defaults('section', 'jadwal')
            ->name('jadwal');

        Route::get('/lokasi-donor', Portal::class)
            ->defaults('section', 'lokasi')
            ->name('lokasi');

        Route::get('/stok-darah', Portal::class)
            ->defaults('section', 'stok')
            ->name('stok');

        Route::get('/riwayat-donor', Portal::class)
            ->defaults('section', 'riwayat')
            ->name('riwayat');

        Route::get('/profil', Portal::class)
            ->defaults('section', 'profil')
            ->name('profil');

        Route::post('/keluar', function (
            Request $request
        ) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        })->name('logout');
    });