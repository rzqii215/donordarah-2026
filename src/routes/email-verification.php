<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify', function () {
    $user = Auth::user();

    if (! $user instanceof User) {
        return redirect()->route('login');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect()
            ->route('home')
            ->with(
                'success',
                'Alamat email Anda sudah terverifikasi.'
            );
    }

    return view('auth.verify-email', [
        'user' => $user,
    ]);
})
    ->middleware('auth')
    ->name('verification.notice');

Route::get(
    '/email/verify/{id}/{hash}',
    function (
        Request $request,
        string $id,
        string $hash
    ) {
        $user = User::query()->findOrFail($id);

        $hashEmail = sha1(
            $user->getEmailForVerification()
        );

        abort_unless(
            hash_equals($hashEmail, $hash),
            403,
            'Link verifikasi email tidak valid.'
        );

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(
                new Verified($user)
            );
        }

        if (
            Auth::check()
            && Auth::id() === $user->getKey()
        ) {
            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'Alamat email berhasil diverifikasi.'
                );
        }

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Alamat email berhasil diverifikasi. Silakan masuk ke akun Anda.'
            );
    }
)
    ->middleware([
        'signed',
        'throttle:6,1',
    ])
    ->name('verification.verify');

Route::post(
    '/email/verification-notification',
    function (Request $request) {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'Alamat email Anda sudah terverifikasi.'
                );
        }

        $user->sendEmailVerificationNotification();

        return back()->with(
            'status',
            'verification-link-sent'
        );
    }
)
    ->middleware([
        'auth',
        'throttle:3,1',
    ])
    ->name('verification.send');