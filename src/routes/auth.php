<?php

use App\Livewire\Auth\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->group(function (): void {
        Route::get('/login', Login::class)
            ->name('login');
    });

Route::post('/logout', function (
    Request $request
) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})
    ->middleware('auth')
    ->name('logout');