<?php

namespace App\Models;

use App\Enums\StatusDistribusiDarah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiDarah extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'dijadwalkan_pada' => 'datetime',
            'status' => StatusDistribusiDarah::class,
            'diserahkan_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
        ];
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(
            PermintaanDarah::class,
            'permintaan_darah_id'
        );
    }

    public function penyiap(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'disiapkan_oleh'
        );
    }

    public function penyerah(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diserahkan_oleh'
        );
    }

    public function dapatDitandaiSiap(): bool
    {
        return $this->status ===
            StatusDistribusiDarah::Dijadwalkan;
    }

    public function dapatDiselesaikan(): bool
    {
        return in_array(
            $this->status,
            [
                StatusDistribusiDarah::Dijadwalkan,
                StatusDistribusiDarah::SiapDiserahkan,
            ],
            true
        );
    }

    public function dapatDibatalkan(): bool
    {
        return in_array(
            $this->status,
            [
                StatusDistribusiDarah::Dijadwalkan,
                StatusDistribusiDarah::SiapDiserahkan,
            ],
            true
        );
    }

    public function dapatDiubah(): bool
    {
        return $this->status ===
            StatusDistribusiDarah::Dijadwalkan;
    }
}