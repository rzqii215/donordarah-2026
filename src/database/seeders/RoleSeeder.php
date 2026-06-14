<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Menambahkan seluruh role utama sistem.
     */
    public function run(): void
    {
        foreach (PeranPengguna::cases() as $peran) {
            Role::firstOrCreate([
                'name' => $peran->value,
                'guard_name' => 'web',
            ]);
        }

        $roleLama = Role::query()
            ->where('name', 'user')
            ->where('guard_name', 'web')
            ->first();

        if (! $roleLama) {
            return;
        }

        $rolePendonor = Role::query()
            ->where('name', PeranPengguna::Pendonor->value)
            ->where('guard_name', 'web')
            ->firstOrFail();

        $roleLama->users()
            ->get()
            ->each(function (User $pengguna) use ($rolePendonor): void {
                $pengguna->syncRoles([$rolePendonor]);
            });

        $roleLama->delete();
    }
}