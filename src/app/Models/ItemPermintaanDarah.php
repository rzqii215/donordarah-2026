<?php

namespace App\Models;

use App\Enums\StatusItemPermintaanDarah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPermintaanDarah extends Model
{
    use HasFactory;

    protected $table = 'item_permintaan_darah';

    protected $fillable = [
        'permintaan_darah_id',
        'kantong_darah_id',
        'status',
        'aktif',
        'dialokasikan_oleh',
        'dialokasikan_pada',
        'dilepas_oleh',
        'dilepas_pada',
        'alasan_pelepasan',
        'didistribusikan_pada',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusItemPermintaanDarah::class,
            'aktif' => 'boolean',
            'dialokasikan_pada' => 'datetime',
            'dilepas_pada' => 'datetime',
            'didistribusikan_pada' => 'datetime',
        ];
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(
            PermintaanDarah::class,
            'permintaan_darah_id'
        );
    }

    public function kantongDarah(): BelongsTo
    {
        return $this->belongsTo(
            KantongDarah::class,
            'kantong_darah_id'
        );
    }

    public function pengalokasi(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dialokasikan_oleh'
        );
    }

    public function pelepas(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dilepas_oleh'
        );
    }

    public function dapatDilepaskan(): bool
    {
        return $this->aktif === true
            && $this->status ===
                StatusItemPermintaanDarah::Dialokasikan;
    }

    public function dapatDidistribusikan(): bool
    {
        return $this->aktif === true
            && $this->status ===
                StatusItemPermintaanDarah::Dialokasikan;
    }
}