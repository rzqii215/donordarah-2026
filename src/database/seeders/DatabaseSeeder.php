<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ProfilPendonorSeeder::class,
            ProfilRumahSakitSeeder::class,
            LokasiDonorSeeder::class,
            JadwalDonorSeeder::class,
            PendaftaranDonorSeeder::class,
            PemeriksaanKesehatanSeeder::class,
            KantongDarahSeeder::class,
            PermintaanDarahSeeder::class,
            ItemPermintaanDarahSeeder::class,
            DistribusiDarahSeeder::class,
        ]);
    }
}