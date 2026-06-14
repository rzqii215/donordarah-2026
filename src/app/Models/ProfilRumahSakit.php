<?php

namespace App\Models;

use App\Enums\StatusVerifikasiRumahSakit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilRumahSakit extends Model
{
    use HasFactory;

    protected $table = 'profil_rumah_sakit';

    protected $fillable = [
        'pengguna_id',
        'kode_rumah_sakit',
        'nama_rumah_sakit',
        'nomor_izin',
        'path_dokumen_izin',
        'nama_penanggung_jawab',
        'jabatan_penanggung_jawab',
        'alamat',
        'provinsi',
        'kota',
        'kecamatan',
        'kode_pos',
        'latitude',
        'longitude',
        'status_verifikasi',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status_verifikasi' =>
                StatusVerifikasiRumahSakit::class,
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'pengguna_id'
        );
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diverifikasi_oleh'
        );
    }

    public function permintaanDarah(): HasMany
    {
        return $this->hasMany(
            PermintaanDarah::class,
            'profil_rumah_sakit_id'
        );
    }
}