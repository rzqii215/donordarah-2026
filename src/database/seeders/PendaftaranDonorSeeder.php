<?php

namespace Database\Seeders;

use App\Models\JadwalDonor;
use App\Models\PendaftaranDonor;
use App\Models\User;
use App\Services\LayananPendaftaranDonor;
use Illuminate\Database\Seeder;

class PendaftaranDonorSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $pendonor = User::query()
            ->where(
                'email',
                'pendonor@donordarah.test'
            )
            ->firstOrFail();

        $jadwal = JadwalDonor::query()
            ->where(
                'kode_jadwal',
                'SCH-202606-000001'
            )
            ->firstOrFail();

        $sudahAda = PendaftaranDonor::withTrashed()
            ->where(
                'jadwal_donor_id',
                $jadwal->id
            )
            ->where(
                'pendonor_id',
                $pendonor->id
            )
            ->exists();

        if ($sudahAda) {
            return;
        }

        app(
            LayananPendaftaranDonor::class
        )->daftar(
            jadwalDonorId: $jadwal->id,
            pendonorId: $pendonor->id,
            data: [
                'jawaban_skrining' => [
                    'sehat_hari_ini' => true,
                    'sedang_minum_obat' => false,
                    'operasi_terakhir' => false,
                    'cukup_tidur' => true,
                    'sudah_makan' => true,
                ],
                'catatan' =>
                    'Data pendaftaran pengujian.',
            ],
        );
    }
}