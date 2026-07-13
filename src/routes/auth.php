<?php

use App\Livewire\Auth\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->group(function (): void {
        /*
         * Login umum dipertahankan untuk kompatibilitas
         * middleware, tautan lama, dan pengujian.
         */
        Route::get(
            '/login',
            Login::class
        )
            ->defaults(
                'portal',
                'umum'
            )
            ->name('login');

        /*
         * Halaman masuk khusus pendonor.
         */
        Route::get(
            '/login/donor',
            Login::class
        )
            ->defaults(
                'portal',
                'donor'
            )
            ->name('login.donor');

        /*
         * Halaman masuk khusus pemohon donor.
         */
        Route::get(
            '/login/pemohon',
            Login::class
        )
            ->defaults(
                'portal',
                'pemohon'
            )
            ->name('login.pemohon');
    });

Route::post(
    '/logout',
    function (
        Request $request
    ) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
)
    ->middleware('auth')
    ->name('logout');
