<?php

use App\Http\Controllers\Auth\KeluarController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
])->group(function (): void {
    Route::match([
        'GET',
        'POST',
    ], '/logout', KeluarController::class)
        ->name('logout');

    Route::match([
        'GET',
        'POST',
    ], '/donor/logout', KeluarController::class)
        ->name('donor.logout');

    Route::match([
        'GET',
        'POST',
    ], '/pemohon-donor/logout', KeluarController::class)
        ->name('pemohon-donor.logout');
});
