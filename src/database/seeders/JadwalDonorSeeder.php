<?php

namespace Database\Seeders;

use App\Enums\StatusJadwalDonor;
use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use App\Models\User;
use Illuminate\Database\Seeder;

class JadwalDonorSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $superAdmin = User::query()
            ->where('email', 'admin@admin.com')
            ->firstOrFail();

        $lokasi = LokasiDonor::query()
            ->where(
                'slug',
                'unit-donor-darah-pusat'
            )
            ->firstOrFail();

        $mulai = now()
            ->addDays(14)
            ->setTime(8, 0);

        JadwalDonor::updateOrCreate(
            [
                'kode_jadwal' =>
                    'SCH-202606-000001',
            ],
            [
                'lokasi_donor_id' =>
                    $lokasi->id,

                'judul' =>
                    'Donor Darah Bersama',

                'deskripsi' =>
                    'Kegiatan donor darah terbuka untuk masyarakat umum.',

                'mulai_pada' =>
                    $mulai,

                'selesai_pada' =>
                    $mulai->copy()->setTime(13, 0),

                'pendaftaran_dibuka_pada' =>
                    now(),

                'pendaftaran_ditutup_pada' =>
                    $mulai->copy()->subDay(),

                'kuota' =>
                    100,

                'status' =>
                    StatusJadwalDonor
                        ::Dipublikasikan,

                'path_banner' =>
                    null,

                'dibuat_oleh' =>
                    $superAdmin->id,

                'dipublikasikan_pada' =>
                    now(),

                'dibatalkan_pada' =>
                    null,

                'alasan_pembatalan' =>
                    null,
            ]
        );
    }
}