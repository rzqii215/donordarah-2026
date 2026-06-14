<?php

namespace App\Models;

use App\Enums\StatusPendaftaranDonor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendaftaranDonor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pendaftaran_donor';

    protected $fillable = [
        'nomor_pendaftaran',
        'jadwal_donor_id',
        'pendonor_id',
        'jawaban_skrining',
        'status',
        'ditinjau_oleh',
        'ditinjau_pada',
        'alasan_penolakan',
        'hadir_pada',
        'dibatalkan_pada',
        'alasan_pembatalan',
        'selesai_pada',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jawaban_skrining' => 'array',
            'status' => StatusPendaftaranDonor::class,
            'ditinjau_pada' => 'datetime',
            'hadir_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
            'selesai_pada' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(
            JadwalDonor::class,
            'jadwal_donor_id'
        );
    }

    public function pendonor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'pendonor_id'
        );
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'ditinjau_oleh'
        );
    }

    public function pemeriksaanKesehatan(): HasOne
    {
        return $this->hasOne(
            PemeriksaanKesehatan::class,
            'pendaftaran_donor_id'
        );
    }

    public function kantongDarah(): HasOne
    {
        return $this->hasOne(
            KantongDarah::class,
            'pendaftaran_donor_id'
        );
    }

    public function scopeAktif(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            StatusPendaftaranDonor
                ::statusMengurangiKuota()
        );
    }

    public function dapatDisetujui(): bool
    {
        return $this->status ===
            StatusPendaftaranDonor::Menunggu;
    }

    public function dapatDitolak(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPendaftaranDonor::Menunggu,
                StatusPendaftaranDonor::Disetujui,
            ],
            true
        );
    }

    public function dapatDicatatHadir(): bool
    {
        return $this->status ===
            StatusPendaftaranDonor::Disetujui;
    }

    public function dapatDibatalkan(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPendaftaranDonor::Menunggu,
                StatusPendaftaranDonor::Disetujui,
            ],
            true
        );
    }

    public function dapatDiperiksa(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPendaftaranDonor::Hadir,
                StatusPendaftaranDonor::Layak,
                StatusPendaftaranDonor::TidakLayak,
            ],
            true
        );
    }

    public function dapatDibuatkanKantongDarah(): bool
    {
        return $this->status ===
                StatusPendaftaranDonor::Layak
            && $this->kantongDarah === null;
    }
}