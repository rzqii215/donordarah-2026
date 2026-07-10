<?php

use App\Http\Controllers\Auth\KeluarController;
use App\Livewire\Donor\Jadwal;
use App\Livewire\Donor\Lokasi;
use App\Livewire\Donor\Portal;
use App\Livewire\Donor\Profil;
use App\Livewire\Donor\Riwayat;
use App\Livewire\Donor\Stok;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth',
])->group(function (): void {
    Route::get('/donor', Portal::class)
        ->defaults('section', 'beranda')
        ->name('donor.beranda');

    Route::get('/donor/jadwal', Jadwal::class)
        ->name('donor.jadwal');

    Route::get('/donor/lokasi', Lokasi::class)
        ->name('donor.lokasi');

    Route::get('/donor/stok', Stok::class)
        ->name('donor.stok');

    Route::get('/donor/riwayat', Riwayat::class)
        ->name('donor.riwayat');

    Route::get('/donor/profil', Profil::class)
        ->name('donor.profil');

    if (! Route::has('donor.logout')) {
        Route::post('/donor/logout', KeluarController::class)
            ->name('donor.logout');
    }
});