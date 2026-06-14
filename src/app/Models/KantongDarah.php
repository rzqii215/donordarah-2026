<?php

namespace App\Models;

use App\Enums\GolonganDarah;
use App\Enums\JenisKomponenDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class KantongDarah extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kantong_darah';

    protected $fillable = [
        'kode_kantong',
        'pendaftaran_donor_id',
        'golongan_darah',
        'rhesus',
        'jenis_komponen',
        'volume_ml',
        'diambil_pada',
        'kedaluwarsa_pada',
        'status_mutu',
        'status',
        'lokasi_penyimpanan',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'alasan_penolakan',
        'didistribusikan_pada',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'golongan_darah' => GolonganDarah::class,
            'rhesus' => RhesusDarah::class,
            'jenis_komponen' => JenisKomponenDarah::class,
            'volume_ml' => 'integer',
            'diambil_pada' => 'datetime',
            'kedaluwarsa_pada' => 'datetime',
            'status_mutu' => StatusMutuKantongDarah::class,
            'status' => StatusKantongDarah::class,
            'diverifikasi_pada' => 'datetime',
            'didistribusikan_pada' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(
            PendaftaranDonor::class,
            'pendaftaran_donor_id'
        );
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diverifikasi_oleh'
        );
    }

    public function itemPermintaanDarah(): HasMany
    {
        return $this->hasMany(
            ItemPermintaanDarah::class,
            'kantong_darah_id'
        );
    }

    public function alokasiAktif(): HasOne
    {
        return $this->hasOne(
            ItemPermintaanDarah::class,
            'kantong_darah_id'
        )->where('aktif', true);
    }

    public function scopeTersedia(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'status',
                StatusKantongDarah::Tersedia->value
            )
            ->where(
                'status_mutu',
                StatusMutuKantongDarah::Lulus->value
            )
            ->where(
                'kedaluwarsa_pada',
                '>',
                now()
            );
    }

    public function scopeGolongan(
        Builder $query,
        GolonganDarah|string $golonganDarah,
        RhesusDarah|string $rhesus
    ): Builder {
        $nilaiGolongan = $golonganDarah instanceof GolonganDarah
            ? $golonganDarah->value
            : $golonganDarah;

        $nilaiRhesus = $rhesus instanceof RhesusDarah
            ? $rhesus->value
            : $rhesus;

        return $query
            ->where(
                'golongan_darah',
                $nilaiGolongan
            )
            ->where(
                'rhesus',
                $nilaiRhesus
            );
    }

    public function sudahKedaluwarsa(): bool
    {
        return $this->kedaluwarsa_pada
            ->lessThanOrEqualTo(now());
    }

    public function dapatDiverifikasi(): bool
    {
        return $this->status ===
                StatusKantongDarah::Menunggu
            && $this->status_mutu ===
                StatusMutuKantongDarah::Menunggu;
    }

    public function dapatDitandaiRusak(): bool
    {
        return $this->status ===
            StatusKantongDarah::Tersedia;
    }

    public function dapatDialokasikan(): bool
    {
        return $this->status ===
                StatusKantongDarah::Tersedia
            && $this->status_mutu ===
                StatusMutuKantongDarah::Lulus
            && ! $this->sudahKedaluwarsa();
    }
}