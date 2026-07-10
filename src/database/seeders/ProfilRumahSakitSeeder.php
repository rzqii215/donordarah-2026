<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProfilRumahSakitSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn(
                'Profil pemohon donor tidak dibuat pada environment production.'
            );

            return;
        }

        $superAdmin = User::updateOrCreate(
            [
                'email' => 'admin@admin.com',
            ],
            [
                'name' => 'Super Admin',
                'nomor_telepon' => '081200000001',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => StatusPengguna::Aktif,
            ]
        );

        $superAdmin->syncRoles([
            PeranPengguna::SuperAdmin->value,
        ]);

        $pemohonDonor = User::updateOrCreate(
            [
                'email' => 'pemohon@donordarah.test',
            ],
            [
                'name' => 'Yayasan Harapan Sehat',
                'nomor_telepon' => '081200000004',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => StatusPengguna::Aktif,
            ]
        );

        $pemohonDonor->syncRoles([
            PeranPengguna::PemohonDonor->value,
        ]);

        ProfilRumahSakit::updateOrCreate(
            [
                'pengguna_id' => $pemohonDonor->id,
            ],
            [
                'kode_rumah_sakit' => 'PMH-2026-000001',
                'nama_rumah_sakit' => 'Yayasan Harapan Sehat',
                'nomor_izin' => 'IZIN-PMH-2026-000001',
                'path_dokumen_izin' => null,
                'nama_penanggung_jawab' => 'Budi Santoso',
                'jabatan_penanggung_jawab' => 'Koordinator Pemohon Donor',
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