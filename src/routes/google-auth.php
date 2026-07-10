<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
])->group(function (): void {
    Route::get('/auth/google/redirect/{tujuan?}', [
        GoogleAuthController::class,
        'redirect',
    ])
        ->whereIn('tujuan', [
            'login',
            'donor',
            'pemohon-donor',
        ])
        ->name('google.redirect');

    Route::get('/auth/google/callback', [
        GoogleAuthController::class,
        'callback',
    ])
        ->name('google.callback');
});