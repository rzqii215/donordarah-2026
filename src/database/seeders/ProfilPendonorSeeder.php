<?php

namespace Database\Seeders;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\RhesusDarah;
use App\Models\ProfilPendonor;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfilPendonorSeeder extends Seeder
{
    /**
     * Membuat profil Pendonor untuk akun pengujian.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $pengguna = User::query()
            ->where('email', 'pendonor@donordarah.test')
            ->firstOrFail();

        ProfilPendonor::updateOrCreate(
            [
                'pengguna_id' => $pengguna->id,
            ],
            [
                'kode_pendonor' => 'DNR-2026-000001',
                'tanggal_lahir' => '1998-05-15',
                'jenis_kelamin' => JenisKelamin::LakiLaki,
                'golongan_darah' => GolonganDarah::O,
                'rhesus' => RhesusDarah::Positif,
                'alamat' => 'Jalan Sehat Bersama Nomor 10',
                'provinsi' => 'DKI Jakarta',
                'kota' => 'Jakarta Selatan',
                'kecamatan' => 'Kebayoran Baru',
                'kode_pos' => '12110',
                'nama_kontak_darurat' => 'Kontak Darurat Pendonor',
                'telepon_kontak_darurat' => '081299999991',
                'terakhir_donor_pada' => null,
                'bersedia_dihubungi' => true,
            ]
        );
    }
}