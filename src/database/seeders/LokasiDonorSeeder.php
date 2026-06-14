<?php

namespace Database\Seeders;

use App\Models\LokasiDonor;
use App\Models\User;
use Illuminate\Database\Seeder;

class LokasiDonorSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $superAdmin = User::query()
            ->where('email', 'admin@admin.com')
            ->firstOrFail();

        LokasiDonor::updateOrCreate(
            [
                'slug' => 'unit-donor-darah-pusat',
            ],
            [
                'nama' => 'Unit Donor Darah Pusat',
                'alamat' => 'Jalan Kesehatan Nomor 10',
                'provinsi' => 'DKI Jakarta',
                'kota' => 'Jakarta Selatan',
                'kecamatan' => 'Kebayoran Baru',
                'kode_pos' => '12110',
                'latitude' => -6.2434790,
                'longitude' => 106.7991450,
                'nama_kontak' => 'Petugas Unit Donor Darah',
                'nomor_kontak' => '0217200001',
                'deskripsi' => 'Lokasi utama pelayanan dan kegiatan donor darah.',
                'aktif' => true,
                'dibuat_oleh' => $superAdmin->id,
            ]
        );

        LokasiDonor::updateOrCreate(
            [
                'slug' => 'aula-kecamatan-pasar-minggu',
            ],
            [
                'nama' => 'Aula Kecamatan Pasar Minggu',
                'alamat' => 'Jalan Raya Pasar Minggu Nomor 30',
                'provinsi' => 'DKI Jakarta',
                'kota' => 'Jakarta Selatan',
                'kecamatan' => 'Pasar Minggu',
                'kode_pos' => '12520',
                'latitude' => -6.2838180,
                'longitude' => 106.8430180,
                'nama_kontak' => 'Koordinator Kegiatan',
                'nomor_kontak' => '081200001001',
                'deskripsi' => 'Lokasi kegiatan donor darah berkala untuk masyarakat umum.',
                'aktif' => true,
                'dibuat_oleh' => $superAdmin->id,
            ]
        );
    }
}