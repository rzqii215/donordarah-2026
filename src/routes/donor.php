<?php

use App\Http\Controllers\Auth\KeluarController;
use App\Livewire\Donor\DaftarDonor;
use App\Livewire\Donor\Jadwal;
use App\Livewire\Donor\Lokasi;
use App\Livewire\Donor\Portal;
use App\Livewire\Donor\Profil;
use App\Livewire\Donor\Riwayat;
use App\Livewire\Donor\Stok;
use Illuminate\Support\Facades\Route;

Route::get('/donor', Portal::class)
    ->defaults('section', 'beranda')
    ->name('donor.beranda');

Route::get('/donor/jadwal', Jadwal::class)
    ->defaults('section', 'jadwal')
    ->name('donor.jadwal');

Route::get(
    '/donor/jadwal/{jadwal}/daftar',
    DaftarDonor::class
)
    ->defaults('section', 'jadwal')
    ->name('donor.jadwal.daftar');

Route::get('/donor/lokasi', Lokasi::class)
    ->defaults('section', 'lokasi')
    ->name('donor.lokasi');

Route::get('/donor/stok', Stok::class)
    ->defaults('section', 'stok')
    ->name('donor.stok');

Route::get('/donor/riwayat', Riwayat::class)
    ->defaults('section', 'riwayat')
    ->name('donor.riwayat');

Route::get('/donor/profil', Profil::class)
    ->defaults('section', 'profil')
    ->name('donor.profil');

Route::post(
    '/donor/logout',
    KeluarController::class
)
    ->name('donor.logout');