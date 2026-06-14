<?php

namespace Database\Seeders;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKelayakanDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Models\PendaftaranDonor;
use App\Models\User;
use App\Services\LayananPemeriksaanKesehatan;
use Illuminate\Database\Seeder;

class PemeriksaanKesehatanSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $petugas = User::query()
            ->where(
                'email',
                'petugas@admin.com'
            )
            ->firstOrFail();

        $pendaftaran =
            PendaftaranDonor::query()
                ->with('pemeriksaanKesehatan')
                ->first();

        if ($pendaftaran === null) {
            return;
        }

        if (
            $pendaftaran->pemeriksaanKesehatan
            !== null
        ) {
            return;
        }

        $pendaftaran->update([
            'status' =>
                StatusPendaftaranDonor::Hadir,

            'ditinjau_oleh' =>
                $petugas->id,

            'ditinjau_pada' =>
                now(),

            'hadir_pada' =>
                now(),
        ]);

        app(
            LayananPemeriksaanKesehatan::class
        )->simpan(
            pendaftaran: $pendaftaran,
            petugasId: $petugas->id,
            data: [
                'berat_badan_kg' => 65.50,
                'tekanan_sistolik' => 120,
                'tekanan_diastolik' => 80,
                'kadar_hemoglobin' => 14.20,
                'suhu_tubuh' => 36.60,
                'denyut_nadi' => 76,
                'golongan_darah' =>
                    GolonganDarah::O,
                'rhesus' =>
                    RhesusDarah::Positif,
                'status_kelayakan' =>
                    StatusKelayakanDonor::Layak,
                'alasan_tidak_layak' => null,
                'catatan_medis' =>
                    'Data pemeriksaan pengujian.',
                'diperiksa_pada' => now(),
            ],
        );
    }
}