<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/auth/google/redirect/{tujuan?}',
    [
        GoogleAuthController::class,
        'redirect',
    ]
)
    ->whereIn('tujuan', [
        'login',
        'donor',
        'pemohon-donor',
    ])
    ->middleware(
        'throttle:10,1'
    )
    ->name('google.redirect');

Route::get(
    '/auth/google/callback',
    [
        GoogleAuthController::class,
        'callback',
    ]
)
    ->middleware(
        'throttle:20,1'
    )
    ->name('google.callback');