<?php

use App\Http\Controllers\Auth\KeluarController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
])->group(function (): void {
    Route::match([
        'GET',
        'POST',
    ], '/pemohon-donor/logout', KeluarController::class)
        ->name('pemohon-donor.logout');
});