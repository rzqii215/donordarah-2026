<?php

namespace App\Models;

use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusPermintaanDarah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiDarah extends Model
{
    protected $table = 'distribusi_darah';

    protected $fillable = [
        'nomor_distribusi',
        'permintaan_darah_id',
        'disiapkan_oleh',
        'dijadwalkan_pada',
        'status',
        'diserahkan_oleh',
        'nama_penerima',
        'jabatan_penerima',
        'nomor_identitas_penerima',
        'path_bukti_serah_terima',
        'diserahkan_pada',
        'dibatalkan_pada',
        'alasan_pembatalan',
        'catatan',
    ];

    protected $casts = [
        'dijadwalkan_pada' => 'datetime',
        'status' => StatusDistribusiDarah::class,
        'diserahkan_pada' => 'datetime',
        'dibatalkan_pada' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (DistribusiDarah $distribusi): void {
            $distribusi->sinkronkanStatusPermintaan();
        });

        static::deleted(function (DistribusiDarah $distribusi): void {
            $distribusi->kembalikanStatusPermintaanSetelahDistribusiDihapus();
        });
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(
            PermintaanDarah::class,
            'permintaan_darah_id'
        );
    }

    public function disiapkanOleh(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'disiapkan_oleh'
        );
    }

    public function diserahkanOleh(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diserahkan_oleh'
        );
    }

    private function sinkronkanStatusPermintaan(): void
    {
        $this->loadMissing('permintaan');

        $permintaan = $this->permintaan;

        if ($permintaan === null) {
            return;
        }

        $statusDistribusi = $this->status instanceof StatusDistribusiDarah
            ? $this->status->value
            : (string) $this->status;

        $statusPermintaanBaru = match ($statusDistribusi) {
            StatusDistribusiDarah::Dijadwalkan->value,
            StatusDistribusiDarah::SiapDiserahkan->value => StatusPermintaanDarah::SiapDiambil,

            StatusDistribusiDarah::Selesai->value => StatusPermintaanDarah::Selesai,

            StatusDistribusiDarah::Dibatalkan->value => StatusPermintaanDarah::Dibatalkan,

            default => null,
        };

        if ($statusPermintaanBaru === null) {
            return;
        }

        $statusPermintaanSekarang = $permintaan->status instanceof StatusPermintaanDarah
            ? $permintaan->status->value
            : (string) $permintaan->status;

        if ($statusPermintaanSekarang === $statusPermintaanBaru->value) {
            return;
        }

        $permintaan
            ->forceFill([
                'status' => $statusPermintaanBaru->value,
            ])
            ->saveQuietly();
    }

    private function kembalikanStatusPermintaanSetelahDistribusiDihapus(): void
    {
        $this->loadMissing('permintaan');

        $permintaan = $this->permintaan;

        if ($permintaan === null) {
            return;
        }

        $masihPunyaDistribusiLain = self::query()
            ->where('permintaan_darah_id', $permintaan->id)
            ->exists();

        if ($masihPunyaDistribusiLain) {
            return;
        }

        $statusPermintaanSekarang = $permintaan->status instanceof StatusPermintaanDarah
            ? $permintaan->status->value
            : (string) $permintaan->status;

        $statusYangBolehDikembalikan = [
            StatusPermintaanDarah::SiapDiambil->value,
            StatusPermintaanDarah::Selesai->value,
            StatusPermintaanDarah::Dibatalkan->value,
        ];

        if (! in_array($statusPermintaanSekarang, $statusYangBolehDikembalikan, true)) {
            return;
        }

        $permintaan
            ->forceFill([
                'status' => StatusPermintaanDarah::Disetujui->value,
            ])
            ->saveQuietly();
    }
}