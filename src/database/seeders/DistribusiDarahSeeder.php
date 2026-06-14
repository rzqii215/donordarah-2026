<?php

namespace Database\Seeders;

use App\Enums\StatusPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\User;
use App\Services\LayananAlokasiDarah;
use App\Services\LayananDistribusiDarah;
use App\Services\LayananPermintaanDarah;
use Illuminate\Database\Seeder;

class DistribusiDarahSeeder extends Seeder
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
            ->first();

        if ($petugas === null) {
            return;
        }

        $permintaan = PermintaanDarah::query()
            ->with([
                'distribusi',
                'itemAktif',
            ])
            ->where(
                'referensi_pasien',
                'PSN-DEMO-0001'
            )
            ->first();

        if ($permintaan === null) {
            return;
        }

        if (
            DistribusiDarah::query()
                ->where(
                    'permintaan_darah_id',
                    $permintaan->id
                )
                ->exists()
        ) {
            return;
        }

        if (
            in_array(
                $permintaan->status,
                [
                    StatusPermintaanDarah::Diajukan,
                    StatusPermintaanDarah::Ditinjau,
                    StatusPermintaanDarah::MenungguStok,
                ],
                true
            )
        ) {
            app(
                LayananPermintaanDarah::class
            )->setujui(
                permintaan: $permintaan,
                petugasId: $petugas->id,
            );

            $permintaan->refresh();
        }

        if (
            $permintaan->status !==
            StatusPermintaanDarah::SiapDiambil
        ) {
            app(
                LayananAlokasiDarah::class
            )->alokasikanOtomatis(
                permintaan: $permintaan,
                petugasId: $petugas->id,
            );

            $permintaan->refresh();
        }

        if (
            $permintaan->status !==
            StatusPermintaanDarah::SiapDiambil
        ) {
            return;
        }

        app(
            LayananDistribusiDarah::class
        )->buat(
            permintaan: $permintaan,
            petugasId: $petugas->id,
            data: [
                'dijadwalkan_pada' =>
                    now()->addDay()->setTime(
                        10,
                        0
                    ),

                'catatan' =>
                    'Distribusi darah pengujian.',
            ],
        );
    }
}