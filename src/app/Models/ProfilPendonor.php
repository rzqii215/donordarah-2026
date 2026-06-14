<?php

namespace App\Models;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\RhesusDarah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilPendonor extends Model
{
    use HasFactory;

    protected $table = 'profil_pendonor';

    protected $fillable = [
        'pengguna_id',
        'kode_pendonor',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'rhesus',
        'alamat',
        'provinsi',
        'kota',
        'kecamatan',
        'kode_pos',
        'nama_kontak_darurat',
        'telepon_kontak_darurat',
        'terakhir_donor_pada',
        'bersedia_dihubungi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'jenis_kelamin' => JenisKelamin::class,
            'golongan_darah' => GolonganDarah::class,
            'rhesus' => RhesusDarah::class,
            'terakhir_donor_pada' => 'datetime',
            'bersedia_dihubungi' => 'boolean',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'pengguna_id'
        );
    }
}