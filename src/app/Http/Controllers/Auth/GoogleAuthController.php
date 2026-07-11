<?php

namespace App\Http\Controllers\Auth;

use App\Enums\StatusPengguna;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteTwoUser;
use Throwable;

class GoogleAuthController extends Controller
{
    private const SESSION_TUJUAN =
        'google_oauth_tujuan';

    private const SESSION_REGISTER =
        'google_register';

    private const REGISTER_EXPIRES_SECONDS = 600;

    /**
     * @var list<string>
     */
    private const ADMIN_ROLES = [
        'super_admin',
        'super-admin',
        'admin',
        'petugas',
    ];

    /**
     * @var list<string>
     */
    private const DONOR_ROLES = [
        'donor',
        'pendonor',
    ];

    /**
     * @var list<string>
     */
    private const PEMOHON_ROLES = [
        'pemohon_donor',
        'pemohon-donor',
        'rumah_sakit',
        'rumah-sakit',
    ];

    public function redirect(
        Request $request,
        ?string $tujuan = null
    ): mixed {
        if (Auth::check()) {
            $user = Auth::user();

            $redirectPath = $user instanceof User
                ? $this->redirectPathByRole($user)
                : '/';

            return redirect()
                ->to($redirectPath)
                ->with(
                    'error',
                    'Anda sudah masuk ke dalam akun.'
                );
        }

        if (! $this->konfigurasiGoogleSiap()) {
            return $this->gagal(
                'Login Google belum dikonfigurasi dengan benar.'
            );
        }

        $tujuan = $this->tujuanValid(
            $tujuan
        );

        /*
         * Data registrasi Google lama tidak boleh
         * digunakan untuk proses OAuth baru.
         */
        $request->session()->forget(
            self::SESSION_REGISTER
        );

        $request->session()->put(
            self::SESSION_TUJUAN,
            $tujuan
        );

        /*
         * Socialite tetap menggunakan mode stateful.
         * Jangan menambahkan stateless().
         */
        return Socialite::driver('google')
            ->redirect();
    }

    public function callback(
        Request $request
    ): RedirectResponse {
        /*
         * Callback tidak boleh digunakan untuk
         * berpindah akun ketika masih login.
         */
        if (Auth::check()) {
            $request->session()->forget([
                self::SESSION_TUJUAN,
                self::SESSION_REGISTER,
            ]);

            $user = Auth::user();

            $redirectPath = $user instanceof User
                ? $this->redirectPathByRole($user)
                : '/';

            return redirect()
                ->to($redirectPath)
                ->with(
                    'error',
                    'Anda sudah masuk ke dalam akun.'
                );
        }

        if (! $this->konfigurasiGoogleSiap()) {
            return $this->gagal(
                'Login Google belum dikonfigurasi dengan benar.'
            );
        }

        try {
            /*
             * Method user() memvalidasi state OAuth
             * yang sebelumnya disimpan dalam session.
             */
            $googleUser = Socialite::driver(
                'google'
            )->user();
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->forget([
                self::SESSION_TUJUAN,
                self::SESSION_REGISTER,
            ]);

            return $this->gagal(
                'Login Google gagal diproses. Silakan coba lagi.'
            );
        }

        $tujuan = $this->tujuanValid(
            (string) $request->session()->pull(
                self::SESSION_TUJUAN,
                'login'
            )
        );

        $googleId = trim(
            (string) $googleUser->getId()
        );

        $email = mb_strtolower(
            trim(
                (string) $googleUser->getEmail()
            )
        );

        if ($googleId === '') {
            return $this->gagal(
                'Akun Google tidak mengembalikan ID pengguna.'
            );
        }

        if ($email === '') {
            return $this->gagal(
                'Akun Google tidak mengembalikan alamat email.'
            );
        }

        if (
            ! $this->emailGoogleTerverifikasi(
                $googleUser
            )
        ) {
            return $this->gagal(
                'Alamat email pada akun Google belum terverifikasi.'
            );
        }

        $userGoogle = User::query()
            ->where(
                'google_id',
                $googleId
            )
            ->first();

        $userEmail = User::query()
            ->where(
                'email',
                $email
            )
            ->first();

        /*
         * Google ID dan email tidak boleh menunjuk
         * ke dua pengguna berbeda.
         */
        if (
            $userGoogle instanceof User
            && $userEmail instanceof User
            && $userGoogle->getKey()
                !== $userEmail->getKey()
        ) {
            return $this->gagal(
                'Identitas Google bertentangan dengan akun pengguna lain.'
            );
        }

        if ($userGoogle instanceof User) {
            return $this->loginPenggunaGoogle(
                request: $request,
                googleUser: $googleUser,
                user: $userGoogle,
                email: $email,
                tujuan: $tujuan
            );
        }

        /*
         * Akun lokal tidak boleh otomatis ditautkan
         * hanya berdasarkan email Google yang sama.
         */
        if ($userEmail instanceof User) {
            $googleIdLama = trim(
                (string) (
                    $userEmail->getAttribute(
                        'google_id'
                    )
                    ?? ''
                )
            );

            if ($googleIdLama !== '') {
                return $this->gagal(
                    'Alamat email ini sudah terhubung dengan akun Google lain.'
                );
            }

            return $this->gagal(
                'Alamat email sudah terdaftar sebagai akun lokal. Masuk menggunakan email dan kata sandi. Penautan Google harus dilakukan dari akun yang sudah masuk.'
            );
        }

        if ($tujuan === 'login') {
            return $this->gagal(
                'Akun Google belum terdaftar. Silakan daftar sebagai pendonor atau pemohon donor terlebih dahulu.'
            );
        }

        return $this->siapkanRegistrasiGoogle(
            request: $request,
            googleUser: $googleUser,
            googleId: $googleId,
            email: $email,
            tujuan: $tujuan
        );
    }

    private function loginPenggunaGoogle(
        Request $request,
        SocialiteUser $googleUser,
        User $user,
        string $email,
        string $tujuan
    ): RedirectResponse {
        if ($tujuan !== 'login') {
            return $this->gagal(
                'Akun Google sudah terdaftar. Gunakan tombol Login dengan Google.'
            );
        }

        $emailTersimpan = mb_strtolower(
            trim(
                (string) $user->email
            )
        );

        /*
         * Perubahan email Google tidak boleh langsung
         * mengubah email akun aplikasi.
         */
        if ($emailTersimpan !== $email) {
            return $this->gagal(
                'Alamat email Google tidak sama dengan email yang tersimpan. Hubungi administrator.'
            );
        }

        $pesanPenolakan = $this->pesanPenolakanAkses(
            $user
        );

        if ($pesanPenolakan !== null) {
            return $this->gagal(
                $pesanPenolakan
            );
        }

        $this->perbaruiDataGoogle(
            user: $user,
            googleUser: $googleUser
        );

        Auth::login(
            $user,
            true
        );

        $request->session()->regenerate();

        return redirect()
            ->intended(
                $this->redirectPathByRole(
                    $user
                )
            );
    }

    private function siapkanRegistrasiGoogle(
        Request $request,
        SocialiteUser $googleUser,
        string $googleId,
        string $email,
        string $tujuan
    ): RedirectResponse {
        if (
            ! in_array(
                $tujuan,
                [
                    'donor',
                    'pemohon-donor',
                ],
                true
            )
        ) {
            return $this->gagal(
                'Tujuan registrasi Google tidak valid.'
            );
        }

        /*
         * Regenerasi ID session setelah OAuth sukses.
         */
        $request->session()->regenerate();

        $authenticatedAt = now()->timestamp;

        $request->session()->put(
            self::SESSION_REGISTER,
            [
                'google_id' => $googleId,

                'name' => trim(
                    (string) (
                        $googleUser->getName()
                        ?: $googleUser->getNickname()
                        ?: ''
                    )
                ),

                'email' => $email,

                'avatar' => trim(
                    (string) (
                        $googleUser->getAvatar()
                        ?: ''
                    )
                ),

                'email_verified' => true,

                'authenticated_at' =>
                    $authenticatedAt,

                'expires_at' =>
                    $authenticatedAt
                    + self::REGISTER_EXPIRES_SECONDS,

                'tujuan' => $tujuan,
            ]
        );

        if ($tujuan === 'donor') {
            return redirect()
                ->to('/register/donor')
                ->with(
                    'success',
                    'Akun Google berhasil diverifikasi. Silakan lengkapi data pendonor.'
                );
        }

        return redirect()
            ->to('/register/pemohon-donor')
            ->with(
                'success',
                'Akun Google berhasil diverifikasi. Silakan lengkapi data pemohon donor.'
            );
    }

    private function perbaruiDataGoogle(
        User $user,
        SocialiteUser $googleUser
    ): void {
        $payload = [];

        $avatar = trim(
            (string) (
                $googleUser->getAvatar()
                ?: ''
            )
        );

        if (
            $avatar !== ''
            && Schema::hasColumn(
                'users',
                'google_avatar'
            )
        ) {
            $payload['google_avatar'] =
                $avatar;
        }

        if (
            blank(
                $user->getAttribute(
                    'email_verified_at'
                )
            )
        ) {
            /*
             * Aman karena Google ID dan email telah
             * cocok serta email Google terverifikasi.
             */
            $payload['email_verified_at'] =
                now();
        }

        if ($payload === []) {
            return;
        }

        $user->forceFill(
            $payload
        )->saveQuietly();
    }

    private function emailGoogleTerverifikasi(
        SocialiteUser $googleUser
    ): bool {
        /*
         * GoogleProvider menghasilkan instance
         * Laravel\Socialite\Two\User.
         */
        if (
            ! $googleUser instanceof
                SocialiteTwoUser
        ) {
            return false;
        }

        $raw = $googleUser->getRaw();

        $verified = $raw['email_verified']
            ?? $raw['verified_email']
            ?? false;

        return filter_var(
            $verified,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private function pesanPenolakanAkses(
        User $user
    ): ?string {
        if (! $this->statusPenggunaAktif($user)) {
            return $this->pesanStatusPengguna(
                $user
            );
        }

        $redirectPath = $this->redirectPathByRole(
            $user
        );

        if ($redirectPath === '/login') {
            return 'Akun tidak memiliki role yang dapat mengakses portal.';
        }

        if (
            $redirectPath === '/donor'
            && ! $user->profilPendonor()
                ->exists()
        ) {
            return 'Profil pendonor belum lengkap. Hubungi administrator.';
        }

        if (
            $redirectPath === '/pemohon-donor'
            && ! $user->profilRumahSakit()
                ->exists()
        ) {
            return 'Profil pemohon donor belum lengkap. Hubungi administrator.';
        }

        return null;
    }

    private function statusPenggunaAktif(
        User $user
    ): bool {
        return $this->nilaiStatusPengguna($user)
            === StatusPengguna::Aktif->value;
    }

    private function pesanStatusPengguna(
        User $user
    ): string {
        return match (
            $this->nilaiStatusPengguna($user)
        ) {
            StatusPengguna::Menunggu->value =>
                'Akun masih menunggu aktivasi.',

            StatusPengguna::TidakAktif->value =>
                'Akun sedang tidak aktif. Hubungi administrator.',

            StatusPengguna::Ditangguhkan->value =>
                'Akun sedang ditangguhkan. Hubungi administrator.',

            StatusPengguna::Ditolak->value =>
                'Akun tidak dapat digunakan karena pengajuan akun ditolak.',

            default =>
                'Akun belum dapat digunakan. Hubungi administrator.',
        };
    }

    private function nilaiStatusPengguna(
        User $user
    ): string {
        $status = $user->status;

        if ($status instanceof \BackedEnum) {
            return strtolower(
                trim(
                    (string) $status->value
                )
            );
        }

        return strtolower(
            trim(
                (string) $status
            )
        );
    }

    private function redirectPathByRole(
        User $user
    ): string {
        $roles = $this->rolesPengguna(
            $user
        );

        if (
            $roles->intersect(
                self::ADMIN_ROLES
            )->isNotEmpty()
        ) {
            return '/admin';
        }

        if (
            $roles->intersect(
                self::PEMOHON_ROLES
            )->isNotEmpty()
        ) {
            return '/pemohon-donor';
        }

        if (
            $roles->intersect(
                self::DONOR_ROLES
            )->isNotEmpty()
        ) {
            return '/donor';
        }

        return '/login';
    }

    /**
     * @return Collection<int, string>
     */
    private function rolesPengguna(
        User $user
    ): Collection {
        return $user->getRoleNames()
            ->map(
                fn (string $role): string =>
                    strtolower(
                        trim($role)
                    )
            )
            ->filter()
            ->values();
    }

    private function konfigurasiGoogleSiap(): bool
    {
        return filled(
            config(
                'services.google.client_id'
            )
        )
            && filled(
                config(
                    'services.google.client_secret'
                )
            )
            && filled(
                config(
                    'services.google.redirect'
                )
            )
            && Schema::hasColumn(
                'users',
                'google_id'
            );
    }

    private function tujuanValid(
        ?string $tujuan
    ): string {
        return match ($tujuan) {
            'donor',
            'pemohon-donor',
            'login' => $tujuan,

            default => 'login',
        };
    }

    private function gagal(
        string $message
    ): RedirectResponse {
        return redirect()
            ->to('/login')
            ->withErrors([
                'email' => $message,
            ]);
    }
}