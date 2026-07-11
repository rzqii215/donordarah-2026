<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleManagementPermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const ROLE_PERMISSIONS = [
        'view_role',
        'view_any_role',
        'create_role',
        'update_role',
        'delete_role',
        'delete_any_role',
    ];

    /**
     * @var list<string>
     */
    private const READ_ONLY_PERMISSIONS = [
        'view_role',
        'view_any_role',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [];

        foreach (self::ROLE_PERMISSIONS as $permissionName) {
            $permissions[$permissionName] =
                Permission::query()->firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
        }

        $superAdmin = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        /*
         * Super Admin mendapatkan seluruh permission role.
         * Permission lain miliknya tidak akan terhapus.
         */
        foreach ($permissions as $permission) {
            $superAdmin->givePermissionTo(
                $permission
            );
        }

        $petugas = Role::query()->firstOrCreate([
            'name' => 'petugas',
            'guard_name' => 'web',
        ]);

        /*
         * Petugas hanya boleh melihat daftar role.
         */
        foreach ($permissions as $name => $permission) {
            if (
                in_array(
                    $name,
                    self::READ_ONLY_PERMISSIONS,
                    true
                )
            ) {
                $petugas->givePermissionTo(
                    $permission
                );

                continue;
            }

            $petugas->revokePermissionTo(
                $permission
            );
        }

        /*
         * Pengguna portal publik tidak boleh
         * mengakses pengelolaan role.
         */
        foreach (
            [
                'donor',
                'pemohon_donor',
            ] as $roleName
        ) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if (! $role instanceof Role) {
                continue;
            }

            foreach ($permissions as $permission) {
                $role->revokePermissionTo(
                    $permission
                );
            }
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}