<?php

namespace Database\Seeders;

use App\Enums\StatusVerifikasiRumahSakit;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfilRumahSakitSeeder extends Seeder
{
    /**
     * Membuat profil Rumah Sakit untuk akun pengujian.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $superAdmin = User::query()
            ->where('email', 'admin@admin.com')
            ->firstOrFail();

        $penggunaRumahSakit = User::query()
            ->where('email', 'rumahsakit@donordarah.test')
            ->firstOrFail();

        ProfilRumahSakit::updateOrCreate(
            [
                'pengguna_id' => $penggunaRumahSakit->id,
            ],
            [
                'kode_rumah_sakit' => 'HSP-2026-000001',
                'nama_rumah_sakit' => 'RS Harapan Sehat',
                'nomor_izin' => 'IZIN-RS-2026-000001',
                'path_dokumen_izin' => null,
                'nama_penanggung_jawab' => 'dr. Ahmad Pratama',
                'jabatan_penanggung_jawab' => 'Direktur Pelayanan',
                'alamat' => 'Jalan Harapan Sehat Nomor 20',
                'provinsi' => 'DKI Jakarta',
                'kota' => 'Jakarta Selatan',
                'kecamatan' => 'Pasar Minggu',
                'kode_pos' => '12520',
                'latitude' => -6.2852000,
                'longitude' => 106.8446000,
                'status_verifikasi' => StatusVerifikasiRumahSakit::Disetujui,
                'diverifikasi_oleh' => $superAdmin->id,
                'diverifikasi_pada' => now(),
                'alasan_penolakan' => null,
            ]
        );
    }
}