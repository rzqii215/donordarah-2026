<?php

namespace App\Models;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKelayakanDonor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeriksaanKesehatan extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaan_kesehatan';

    protected $fillable = [
        'pendaftaran_donor_id',
        'diperiksa_oleh',
        'berat_badan_kg',
        'tekanan_sistolik',
        'tekanan_diastolik',
        'kadar_hemoglobin',
        'suhu_tubuh',
        'denyut_nadi',
        'golongan_darah',
        'rhesus',
        'status_kelayakan',
        'alasan_tidak_layak',
        'catatan_medis',
        'diperiksa_pada',
    ];

    protected function casts(): array
    {
        return [
            'berat_badan_kg' => 'decimal:2',
            'tekanan_sistolik' => 'integer',
            'tekanan_diastolik' => 'integer',
            'kadar_hemoglobin' => 'decimal:2',
            'suhu_tubuh' => 'decimal:2',
            'denyut_nadi' => 'integer',
            'golongan_darah' => GolonganDarah::class,
            'rhesus' => RhesusDarah::class,
            'status_kelayakan' =>
                StatusKelayakanDonor::class,
            'diperiksa_pada' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(
            PendaftaranDonor::class,
            'pendaftaran_donor_id'
        );
    }

    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diperiksa_oleh'
        );
    }
}