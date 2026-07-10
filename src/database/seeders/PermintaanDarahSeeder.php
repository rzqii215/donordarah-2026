<?php

namespace Database\Seeders;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Services\LayananPermintaanDarah;
use Illuminate\Database\Seeder;

class PermintaanDarahSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $pemohonDonor = ProfilRumahSakit::query()
            ->where(
                'kode_rumah_sakit',
                'PMH-2026-000001'
            )
            ->first();

        if ($pemohonDonor === null) {
            $pemohonDonor = ProfilRumahSakit::query()
                ->latest('id')
                ->first();
        }

        if ($pemohonDonor === null) {
            return;
        }

        $sudahAda = PermintaanDarah::withTrashed()
            ->where(
                'profil_rumah_sakit_id',
                $pemohonDonor->id
            )
            ->where(
                'referensi_pasien',
                'PGJ-DEMO-0001'
            )
            ->exists();

        if ($sudahAda) {
            return;
        }

        app(
            LayananPermintaanDarah::class
        )->buat(
            rumahSakit: $pemohonDonor,
            data: [
                'referensi_pasien' =>
                    'PGJ-DEMO-0001',

                'nama_dokter' =>
                    'Budi Santoso',

                'golongan_darah' =>
                    GolonganDarah::O,

                'rhesus' =>
                    RhesusDarah::Positif,

                'jumlah_kantong' =>
                    1,

                'tingkat_urgensi' =>
                    TingkatUrgensiPermintaanDarah::Mendesak,

                'dibutuhkan_pada' =>
                    now()->addDays(2),

                'path_dokumen_permintaan' =>
                    null,

                'catatan' =>
                    'Data pengajuan kebutuhan donor pengujian.',
            ],
        );
    }
}