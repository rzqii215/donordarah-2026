<?php

namespace App\Models;

use App\Enums\StatusPengguna;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements
    FilamentUser,
    HasAvatar,
    MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'avatar_url',
        'name',
        'email',
        'nomor_telepon',
        'password',
        'status',
        'terakhir_login_pada',
        'ip_terakhir_login',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => StatusPengguna::class,
            'terakhir_login_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(
            function (User $user): void {
                if ($user->hasVerifiedEmail()) {
                    return;
                }

                $user->sendEmailVerificationNotification();
            }
        );
    }

    public function profilPendonor(): HasOne
    {
        return $this->hasOne(
            ProfilPendonor::class,
            'pengguna_id'
        );
    }

    public function profilRumahSakit(): HasOne
    {
        return $this->hasOne(
            ProfilRumahSakit::class,
            'pengguna_id'
        );
    }

    public function rumahSakitDiverifikasi(): HasMany
    {
        return $this->hasMany(
            ProfilRumahSakit::class,
            'diverifikasi_oleh'
        );
    }

    public function lokasiDonorDibuat(): HasMany
    {
        return $this->hasMany(
            LokasiDonor::class,
            'dibuat_oleh'
        );
    }

    public function jadwalDonorDibuat(): HasMany
    {
        return $this->hasMany(
            JadwalDonor::class,
            'dibuat_oleh'
        );
    }

    public function pendaftaranDonor(): HasMany
    {
        return $this->hasMany(
            PendaftaranDonor::class,
            'pendonor_id'
        );
    }

    public function pendaftaranDitinjau(): HasMany
    {
        return $this->hasMany(
            PendaftaranDonor::class,
            'ditinjau_oleh'
        );
    }

    public function pemeriksaanDilakukan(): HasMany
    {
        return $this->hasMany(
            PemeriksaanKesehatan::class,
            'diperiksa_oleh'
        );
    }

    public function kantongDarahDiverifikasi(): HasMany
    {
        return $this->hasMany(
            KantongDarah::class,
            'diverifikasi_oleh'
        );
    }

    public function alokasiDarahDilakukan(): HasMany
    {
        return $this->hasMany(
            ItemPermintaanDarah::class,
            'dialokasikan_oleh'
        );
    }

    public function alokasiDarahDilepaskan(): HasMany
    {
        return $this->hasMany(
            ItemPermintaanDarah::class,
            'dilepas_oleh'
        );
    }

    public function distribusiDarahDisiapkan(): HasMany
    {
        return $this->hasMany(
            DistribusiDarah::class,
            'disiapkan_oleh'
        );
    }

    public function distribusiDarahDiserahkan(): HasMany
    {
        return $this->hasMany(
            DistribusiDarah::class,
            'diserahkan_oleh'
        );
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (filled($this->avatar_url)) {
            return asset(
                'storage/' . ltrim(
                    $this->avatar_url,
                    '/'
                )
            );
        }

        $hash = md5(
            strtolower(
                trim($this->email)
            )
        );

        return sprintf(
            'https://www.gravatar.com/avatar/%s?d=mp&r=g&s=250',
            $hash
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return $this->hasAnyRole([
            'super_admin',
            'super-admin',
            'admin',
            'petugas',
        ]);
    }

    public function routeNotificationForMail(
        ?Notification $notification = null
    ): array|string {
        if (
            app()->environment('local')
            && (bool) config(
                'mail.testing_recipient.enabled',
                false
            )
        ) {
            $testingAddress = trim(
                (string) config(
                    'mail.testing_recipient.address',
                    ''
                )
            );

            if ($testingAddress !== '') {
                /*
                 * Resend testing membutuhkan alamat penerima
                 * polos tanpa display name.
                 */
                return $testingAddress;
            }
        }

        return (string) $this->email;
    }

    public function sendPasswordResetNotification(
        $token
    ): void {
        $this->notify(
            new ResetPasswordNotification($token)
        );
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(
            new VerifyEmailNotification()
        );
    }
}