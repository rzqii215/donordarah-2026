<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * DatabaseSeeder hanya menyiapkan data sistem.
     *
     * Data pengguna, lokasi, jadwal, stok, permintaan,
     * dan distribusi harus dimasukkan melalui aplikasi.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);
    }
}