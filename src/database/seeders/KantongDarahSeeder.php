<?php

namespace Database\Seeders;

use App\Enums\JenisKomponenDarah;
use App\Models\KantongDarah;
use App\Models\PendaftaranDonor;
use App\Models\User;
use App\Services\LayananKantongDarah;
use Illuminate\Database\Seeder;

class KantongDarahSeeder extends Seeder
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
                ->with([
                    'pemeriksaanKesehatan',
                    'kantongDarah',
                ])
                ->whereHas(
                    'pemeriksaanKesehatan'
                )
                ->first();

        if ($pendaftaran === null) {
            return;
        }

        if (
            KantongDarah::withTrashed()
                ->where(
                    'pendaftaran_donor_id',
                    $pendaftaran->id
                )
                ->exists()
        ) {
            return;
        }

        $kantong = app(
            LayananKantongDarah::class
        )->buat(
            pendaftaran: $pendaftaran,
            data: [
                'golongan_darah' =>
                    $pendaftaran
                        ->pemeriksaanKesehatan
                        ->golongan_darah,

                'rhesus' =>
                    $pendaftaran
                        ->pemeriksaanKesehatan
                        ->rhesus,

                'jenis_komponen' =>
                    JenisKomponenDarah::DarahUtuh,

                'volume_ml' =>
                    350,

                'diambil_pada' =>
                    now(),

                'kedaluwarsa_pada' =>
                    now()->addDays(30),

                'lokasi_penyimpanan' =>
                    'Lemari Pendingin A-01',

                'catatan' =>
                    'Data kantong darah pengujian.',
            ],
        );

        app(
            LayananKantongDarah::class
        )->luluskanMutu(
            kantong: $kantong,
            petugasId: $petugas->id,
        );
    }
}