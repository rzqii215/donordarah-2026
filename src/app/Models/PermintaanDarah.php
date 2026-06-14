<?php

namespace App\Models;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusItemPermintaanDarah;
use App\Enums\StatusPermintaanDarah;
use App\Enums\TingkatUrgensiPermintaanDarah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermintaanDarah extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'permintaan_darah';

    protected $fillable = [
        'nomor_permintaan',
        'profil_rumah_sakit_id',
        'referensi_pasien',
        'nama_dokter',
        'golongan_darah',
        'rhesus',
        'jumlah_kantong',
        'tingkat_urgensi',
        'dibutuhkan_pada',
        'path_dokumen_permintaan',
        'status',
        'ditinjau_oleh',
        'ditinjau_pada',
        'disetujui_pada',
        'siap_diambil_pada',
        'selesai_pada',
        'dibatalkan_pada',
        'alasan_penolakan',
        'alasan_pembatalan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'golongan_darah' => GolonganDarah::class,
            'rhesus' => RhesusDarah::class,
            'jumlah_kantong' => 'integer',
            'tingkat_urgensi' =>
                TingkatUrgensiPermintaanDarah::class,
            'dibutuhkan_pada' => 'datetime',
            'status' => StatusPermintaanDarah::class,
            'ditinjau_pada' => 'datetime',
            'disetujui_pada' => 'datetime',
            'siap_diambil_pada' => 'datetime',
            'selesai_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
        ];
    }

    public function rumahSakit(): BelongsTo
    {
        return $this->belongsTo(
            ProfilRumahSakit::class,
            'profil_rumah_sakit_id'
        );
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'ditinjau_oleh'
        );
    }

    public function itemPermintaanDarah(): HasMany
    {
        return $this->hasMany(
            ItemPermintaanDarah::class,
            'permintaan_darah_id'
        );
    }

    public function itemAktif(): HasMany
    {
        return $this->itemPermintaanDarah()
            ->where('aktif', true);
    }

    public function itemDistribusi(): HasMany
    {
        return $this->itemPermintaanDarah()
            ->whereIn(
                'status',
                [
                    StatusItemPermintaanDarah
                        ::Dialokasikan
                        ->value,

                    StatusItemPermintaanDarah
                        ::Didistribusikan
                        ->value,
                ]
            );
    }

    public function distribusi(): HasOne
    {
        return $this->hasOne(
            DistribusiDarah::class,
            'permintaan_darah_id'
        );
    }

    public function scopeAktif(
        Builder $query
    ): Builder {
        return $query->whereNotIn(
            'status',
            [
                StatusPermintaanDarah::Selesai->value,
                StatusPermintaanDarah::Ditolak->value,
                StatusPermintaanDarah::Dibatalkan->value,
            ]
        );
    }

    public function dapatDiubah(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPermintaanDarah::Draf,
                StatusPermintaanDarah::Diajukan,
            ],
            true
        );
    }

    public function dapatDitinjau(): bool
    {
        return $this->status ===
            StatusPermintaanDarah::Diajukan;
    }

    public function dapatDisetujui(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPermintaanDarah::Diajukan,
                StatusPermintaanDarah::Ditinjau,
                StatusPermintaanDarah::MenungguStok,
            ],
            true
        );
    }

    public function dapatDitolak(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPermintaanDarah::Diajukan,
                StatusPermintaanDarah::Ditinjau,
                StatusPermintaanDarah::MenungguStok,
            ],
            true
        );
    }

    public function dapatDibatalkan(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPermintaanDarah::Draf,
                StatusPermintaanDarah::Diajukan,
                StatusPermintaanDarah::Ditinjau,
                StatusPermintaanDarah::MenungguStok,
                StatusPermintaanDarah::Disetujui,
            ],
            true
        );
    }

    public function dapatDialokasikan(): bool
    {
        return in_array(
            $this->status,
            [
                StatusPermintaanDarah::Disetujui,
                StatusPermintaanDarah::MenungguStok,
                StatusPermintaanDarah::SiapDiambil,
            ],
            true
        );
    }

    public function dapatDibuatkanDistribusi(): bool
    {
        return $this->status ===
                StatusPermintaanDarah::SiapDiambil
            && $this->kebutuhanSudahTerpenuhi()
            && $this->distribusi === null;
    }

    public function jumlahKantongDialokasikan(): int
    {
        if ($this->relationLoaded('itemAktif')) {
            return $this->itemAktif->count();
        }

        return $this->itemAktif()->count();
    }

    public function sisaKebutuhanKantong(): int
    {
        return max(
            0,
            $this->jumlah_kantong
                - $this->jumlahKantongDialokasikan()
        );
    }

    public function kebutuhanSudahTerpenuhi(): bool
    {
        return $this->sisaKebutuhanKantong() === 0;
    }
}