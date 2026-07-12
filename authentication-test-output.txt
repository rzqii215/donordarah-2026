<?php

namespace Tests\Feature;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Livewire\Auth\Login;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }

    /**
     * @test
     */
    public function halaman_login_dapat_dibuka_oleh_pengunjung(): void
    {
        $this->get('/login')
            ->assertOk();
    }

    /**
     * @test
     */
    public function pengunjung_halaman_utama_diarahkan_ke_login(): void
    {
        $this->get('/')
            ->assertRedirectToRoute('login');

        $this->assertGuest();
    }

    /**
     * @return array<string, array{0: PeranPengguna, 1: string}>
     */
    public static function roleRedirectProvider(): array
    {
        return [
            'pendonor' => [
                PeranPengguna::Pendonor,
                '/donor',
            ],

            'pemohon donor' => [
                PeranPengguna::PemohonDonor,
                '/pemohon-donor',
            ],

            'petugas' => [
                PeranPengguna::Petugas,
                '/admin',
            ],

            'super admin' => [
                PeranPengguna::SuperAdmin,
                '/admin',
            ],
        ];
    }

    /**
     * @test
     */
    #[DataProvider('roleRedirectProvider')]
    public function pengguna_aktif_diarahkan_ke_portal_berdasarkan_role(
        PeranPengguna $peran,
        string $tujuan
    ): void {
        $user = $this->buatPengguna(
            $peran
        );

        Livewire::test(Login::class)
            ->set(
                'email',
                $user->email
            )
            ->set(
                'password',
                'password'
            )
            ->call('authenticate')
            ->assertRedirect($tujuan);

        $this->assertAuthenticatedAs(
            $user
        );
    }

    /**
     * @test
     */
    public function login_mencatat_waktu_dan_alamat_ip_pengguna(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor
        );

        Livewire::test(Login::class)
            ->set(
                'email',
                $user->email
            )
            ->set(
                'password',
                'password'
            )
            ->call('authenticate')
            ->assertRedirect('/donor');

        $user->refresh();

        $this->assertNotNull(
            $user->terakhir_login_pada
        );

        $this->assertSame(
            '127.0.0.1',
            $user->ip_terakhir_login
        );
    }

    /**
     * @test
     */
    public function kata_sandi_yang_salah_ditolak(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor
        );

        Livewire::test(Login::class)
            ->set(
                'email',
                $user->email
            )
            ->set(
                'password',
                'kata-sandi-yang-salah'
            )
            ->call('authenticate')
            ->assertHasErrors([
                'email',
            ]);

        $this->assertGuest();
    }

    /**
     * @return array<string, array{0: StatusPengguna}>
     */
    public static function statusTidakAktifProvider(): array
    {
        return [
            'menunggu aktivasi' => [
                StatusPengguna::Menunggu,
            ],

            'tidak aktif' => [
                StatusPengguna::TidakAktif,
            ],

            'ditangguhkan' => [
                StatusPengguna::Ditangguhkan,
            ],

            'ditolak' => [
                StatusPengguna::Ditolak,
            ],
        ];
    }

    /**
     * @test
     */
    #[DataProvider('statusTidakAktifProvider')]
    public function login_pengguna_dengan_status_tidak_aktif_ditolak(
        StatusPengguna $status
    ): void {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor,
            $status
        );

        Livewire::test(Login::class)
            ->set(
                'email',
                $user->email
            )
            ->set(
                'password',
                'password'
            )
            ->call('authenticate')
            ->assertRedirect(
                route('login')
            );

        $this->assertGuest();
    }

    /**
     * @test
     */
    public function akun_tanpa_role_tidak_dapat_mempertahankan_sesi_login(): void
    {
        $user = User::factory()->create([
            'status' => StatusPengguna::Aktif->value,

            'email_verified_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set(
                'email',
                $user->email
            )
            ->set(
                'password',
                'password'
            )
            ->call('authenticate')
            ->assertRedirect(
                route('login')
            );

        $this->assertGuest();
    }

    /**
     * @test
     */
    public function akun_belum_terverifikasi_diarahkan_ke_halaman_verifikasi(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor,
            StatusPengguna::Aktif,
            false
        );

        Livewire::test(Login::class)
            ->set(
                'email',
                $user->email
            )
            ->set(
                'password',
                'password'
            )
            ->call('authenticate')
            ->assertRedirect(
                route('verification.notice')
            );

        $this->assertAuthenticatedAs(
            $user
        );
    }

    /**
     * @test
     */
    public function akun_belum_terverifikasi_tidak_dapat_membuka_portal(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor,
            StatusPengguna::Aktif,
            false
        );

        $this->actingAs($user)
            ->get('/donor')
            ->assertRedirectToRoute(
                'verification.notice'
            );

        $this->assertAuthenticatedAs(
            $user
        );
    }

    /**
     * @test
     */
    public function akun_ditangguhkan_dikeluarkan_saat_membuka_portal(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor,
            StatusPengguna::Ditangguhkan
        );

        $this->actingAs($user)
            ->get('/donor')
            ->assertRedirectToRoute('login')
            ->assertSessionHas(
                'error',
                'Akun sedang ditangguhkan. Hubungi administrator.'
            );

        $this->assertGuest();
    }

    /**
     * @test
     */
    public function pendonor_tidak_dapat_membuka_portal_pemohon_donor(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor
        );

        $this->actingAs($user)
            ->get('/pemohon-donor/beranda')
            ->assertRedirect('/donor')
            ->assertSessionHas(
                'error',
                'Anda tidak memiliki akses ke portal pemohon donor.'
            );

        $this->assertAuthenticatedAs(
            $user
        );
    }

    /**
     * @test
     */
    public function pemohon_donor_tidak_dapat_membuka_portal_pendonor(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::PemohonDonor
        );

        $this->actingAs($user)
            ->get('/donor')
            ->assertRedirect('/pemohon-donor')
            ->assertSessionHas(
                'error',
                'Anda tidak memiliki akses ke portal pendonor.'
            );

        $this->assertAuthenticatedAs(
            $user
        );
    }

    /**
     * @test
     */
    public function akun_belum_terverifikasi_menerima_notifikasi_verifikasi(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor,
            StatusPengguna::Aktif,
            false
        );

        Notification::assertSentTo(
            $user,
            VerifyEmailNotification::class
        );
    }

    /**
     * @test
     */
    public function link_bertanda_tangan_memverifikasi_email(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor,
            StatusPengguna::Aktif,
            false
        );

        $verificationUrl =
            URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->getKey(),

                    'hash' => sha1(
                        $user->getEmailForVerification()
                    ),
                ]
            );

        $this->get($verificationUrl)
            ->assertRedirectToRoute('login');

        $user->refresh();

        $this->assertTrue(
            $user->hasVerifiedEmail()
        );

        $this->assertNotNull(
            $user->email_verified_at
        );
    }

    /**
     * @test
     */
    public function link_dengan_hash_email_salah_ditolak(): void
    {
        $user = $this->buatPengguna(
            PeranPengguna::Pendonor,
            StatusPengguna::Aktif,
            false
        );

        $verificationUrl =
            URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->getKey(),

                    'hash' => sha1(
                        'alamat-email-yang-salah@example.com'
                    ),
                ]
            );

        $this->get($verificationUrl)
            ->assertForbidden();

        $user->refresh();

        $this->assertFalse(
            $user->hasVerifiedEmail()
        );
    }

    private function buatPengguna(
        PeranPengguna $peran,
        StatusPengguna $status =
            StatusPengguna::Aktif,
        bool $emailTerverifikasi = true
    ): User {
        Role::findOrCreate(
            $peran->value,
            'web'
        );

        $user = User::factory()->create([
            'status' => $status->value,

            'email_verified_at' => $emailTerverifikasi
                    ? now()
                    : null,
        ]);

        $user->assignRole(
            $peran->value
        );

        return $user;
    }
}
