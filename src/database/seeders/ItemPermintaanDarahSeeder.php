<?php

namespace Database\Seeders;

use App\Enums\StatusPermintaanDarah;
use App\Models\ItemPermintaanDarah;
use App\Models\PermintaanDarah;
use App\Models\User;
use App\Services\LayananAlokasiDarah;
use App\Services\LayananPermintaanDarah;
use Illuminate\Database\Seeder;

class ItemPermintaanDarahSeeder extends Seeder
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

        $pengajuan = PermintaanDarah::query()
            ->where(
                'referensi_pasien',
                'PGJ-DEMO-0001'
            )
            ->first();

        if ($pengajuan === null) {
            return;
        }

        $sudahDialokasikan = ItemPermintaanDarah::query()
            ->where(
                'permintaan_darah_id',
                $pengajuan->id
            )
            ->where(
                'aktif',
                true
            )
            ->exists();

        if ($sudahDialokasikan) {
            return;
        }

        if (
            in_array(
                $pengajuan->status,
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
                permintaan: $pengajuan,
                petugasId: $petugas->id,
            );

            $pengajuan->refresh();
        }

        app(
            LayananAlokasiDarah::class
        )->alokasikanOtomatis(
            permintaan: $pengajuan,
            petugasId: $petugas->id,
        );
    }
}