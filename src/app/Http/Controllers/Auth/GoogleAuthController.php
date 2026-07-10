<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request, ?string $tujuan = null): mixed
    {
        $tujuan = $this->tujuanValid($tujuan);

        $request->session()->put(
            'google_oauth_tujuan',
            $tujuan
        );

        return Socialite::driver('google')
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->user();
        } catch (Throwable) {
            return redirect()
                ->to('/login')
                ->withErrors([
                    'email' => 'Login Google gagal diproses. Silakan coba lagi.',
                ]);
        }

        $googleId = trim((string) $googleUser->getId());

        $email = mb_strtolower(
            trim((string) $googleUser->getEmail())
        );

        if ($email === '') {
            return redirect()
                ->to('/login')
                ->withErrors([
                    'email' => 'Akun Google tidak mengembalikan alamat email.',
                ]);
        }

        if ($googleId === '') {
            return redirect()
                ->to('/login')
                ->withErrors([
                    'email' => 'Akun Google tidak mengembalikan ID pengguna.',
                ]);
        }

        $tujuan = $this->tujuanValid(
            (string) $request->session()->pull(
                'google_oauth_tujuan',
                'login'
            )
        );

        [$user, $pesanKonflik] = $this->userDariGoogleAtauEmail(
            googleId: $googleId,
            email: $email
        );

        if ($pesanKonflik !== null) {
            return redirect()
                ->to('/login')
                ->withErrors([
                    'email' => $pesanKonflik,
                ]);
        }

        if (
            $user !== null
            && $tujuan !== 'login'
        ) {
            return redirect()
                ->to('/login')
                ->withErrors([
                    'email' => 'Alamat email Google ini sudah terdaftar. Silakan masuk menggunakan Google atau email dan kata sandi.',
                ]);
        }

        if ($user !== null) {
            if (! $this->bolehHubungkanGoogle($user, $googleId)) {
                return redirect()
                    ->to('/login')
                    ->withErrors([
                        'email' => 'Akun ini sudah terhubung dengan akun Google lain.',
                    ]);
            }

            $this->simpanDataGoogleKeUser(
                user: $user,
                googleUser: $googleUser,
                googleId: $googleId,
                email: $email
            );

            Auth::login($user, true);

            $request->session()->regenerate();

            return redirect()
                ->intended(
                    $this->redirectPathByRole($user)
                );
        }

        if ($tujuan === 'login') {
            return redirect()
                ->to('/login')
                ->withErrors([
                    'email' => 'Akun Google belum terdaftar. Silakan daftar sebagai pendonor atau pemohon donor terlebih dahulu.',
                ]);
        }

        $request->session()->put('google_register', [
            'google_id' => $googleId,
            'name' => (string) (
                $googleUser->getName()
                ?: $googleUser->getNickname()
                ?: ''
            ),
            'email' => $email,
            'avatar' => (string) (
                $googleUser->getAvatar()
                ?: ''
            ),
            'tujuan' => $tujuan,
        ]);

        if ($tujuan === 'donor') {
            return redirect()
                ->to('/register/donor')
                ->with(
                    'success',
                    'Akun Google berhasil diverifikasi. Silakan lengkapi data pendonor.'
                );
        }

        if ($tujuan === 'pemohon-donor') {
            return redirect()
                ->to('/register/pemohon-donor')
                ->with(
                    'success',
                    'Akun Google berhasil diverifikasi. Silakan lengkapi data pemohon donor.'
                );
        }

        return redirect()
            ->to('/login')
            ->withErrors([
                'email' => 'Tujuan autentikasi Google tidak valid.',
            ]);
    }

    private function tujuanValid(?string $tujuan): string
    {
        return match ($tujuan) {
            'donor',
            'pemohon-donor',
            'login' => $tujuan,

            default => 'login',
        };
    }

    /**
     * @return array{0: ?User, 1: ?string}
     */
    private function userDariGoogleAtauEmail(
        string $googleId,
        string $email
    ): array {
        $userGoogle = $this->cariUserDenganGoogleId($googleId);
        $userEmail = $this->cariUserDenganEmail($email);

        if (
            $userGoogle instanceof User
            && $userEmail instanceof User
            && $userGoogle->getKey() !== $userEmail->getKey()
        ) {
            return [
                null,
                'Akun Google ini sudah terhubung dengan pengguna lain. Gunakan akun Google atau email yang sesuai.',
            ];
        }

        return [
            $userGoogle ?: $userEmail,
            null,
        ];
    }

    private function cariUserDenganGoogleId(string $googleId): ?User
    {
        if (
            $googleId === ''
            || ! Schema::hasColumn('users', 'google_id')
        ) {
            return null;
        }

        return User::query()
            ->where('google_id', $googleId)
            ->first();
    }

    private function cariUserDenganEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }

    private function bolehHubungkanGoogle(
        User $user,
        string $googleId
    ): bool {
        if (! Schema::hasColumn('users', 'google_id')) {
            return true;
        }

        $googleIdLama = trim(
            (string) (
                $user->getAttribute('google_id')
                ?? ''
            )
        );

        if ($googleIdLama === '') {
            return true;
        }

        return $googleIdLama === $googleId;
    }

    private function simpanDataGoogleKeUser(
        User $user,
        SocialiteUser $googleUser,
        string $googleId,
        string $email
    ): void {
        $payload = [];

        if (Schema::hasColumn('users', 'google_id')) {
            $payload['google_id'] = $googleId;
        }

        if (Schema::hasColumn('users', 'google_avatar')) {
            $payload['google_avatar'] = (string) (
                $googleUser->getAvatar()
                ?: ''
            );
        }

        if (
            Schema::hasColumn('users', 'email_verified_at')
            && blank($user->getAttribute('email_verified_at'))
        ) {
            $payload['email_verified_at'] = now();
        }

        if (
            Schema::hasColumn('users', 'email')
            && blank($user->getAttribute('email'))
        ) {
            $payload['email'] = $email;
        }

        if ($payload === []) {
            return;
        }

        $user->forceFill($payload);
        $user->save();
    }

    private function redirectPathByRole(User $user): string
    {
        $roles = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()
                ->map(fn (string $role): string => strtolower($role))
            : collect();

        if (
            $roles->contains('super_admin')
            || $roles->contains('super-admin')
            || $roles->contains('admin')
            || $roles->contains('petugas')
        ) {
            return '/admin';
        }

        $punyaRolePemohon = $roles->contains(
            fn (string $role): bool =>
                $role === 'rumah_sakit'
                || $role === 'pemohon_donor'
                || $role === 'pemohon-donor'
                || str_contains($role, 'pemohon')
                || str_contains($role, 'rumah')
                || str_contains($role, 'sakit')
                || str_contains($role, 'hospital')
        );

        if ($punyaRolePemohon) {
            return '/pemohon-donor';
        }

        $punyaRolePendonor = $roles->contains(
            fn (string $role): bool =>
                $role === 'donor'
                || $role === 'pendonor'
                || str_contains($role, 'pendonor')
        );

        if ($punyaRolePendonor) {
            return '/donor';
        }

        return '/login';
    }
}