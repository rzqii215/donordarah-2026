<?php

use App\Livewire\Auth\RegisterDonor;
use App\Livewire\Auth\RegisterPemohonDonor;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'guest',
])->group(function (): void {
    Route::redirect('/register', '/register/donor')
        ->name('register.index');

    Route::get('/register/donor', RegisterDonor::class)
        ->name('register.donor');

    Route::get('/register/pemohon-donor', RegisterPemohonDonor::class)
        ->name('register.pemohon-donor');
});