<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Membuat akun pengujian untuk development.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn(
                'UserSeeder dilewati karena aplikasi sedang berjalan pada environment production.'
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

        $petugas = User::updateOrCreate(
            [
                'email' => 'petugas@admin.com',
            ],
            [
                'name' => 'Petugas Donor Darah',
                'nomor_telepon' => '081200000002',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => StatusPengguna::Aktif,
            ]
        );

        $petugas->syncRoles([
            PeranPengguna::Petugas->value,
        ]);

        $pendonor = User::updateOrCreate(
            [
                'email' => 'pendonor@donordarah.test',
            ],
            [
                'name' => 'Pendonor Pengujian',
                'nomor_telepon' => '081200000003',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => StatusPengguna::Aktif,
            ]
        );

        $pendonor->syncRoles([
            PeranPengguna::Pendonor->value,
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
    }
}