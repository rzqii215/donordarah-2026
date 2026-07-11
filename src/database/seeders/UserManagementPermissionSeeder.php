<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserManagementPermissionSeeder extends Seeder
{
    private const GUARD_NAME = 'web';

    /**
     * @var array<int, string>
     */
    private const VIEW_PERMISSIONS = [
        'view_user',
        'view_any_user',
    ];

    /**
     * @var array<int, string>
     */
    private const MANAGE_PERMISSIONS = [
        'create_user',
        'update_user',
        'delete_user',
        'delete_any_user',
    ];

    public function run(): void
    {
        $permissionRegistrar = app(
            PermissionRegistrar::class
        );

        $permissionRegistrar
            ->forgetCachedPermissions();

        $semuaPermissionUser = collect([
            ...self::VIEW_PERMISSIONS,
            ...self::MANAGE_PERMISSIONS,
        ])
            ->map(
                fn (string $permissionName): Permission =>
                    Permission::findOrCreate(
                        $permissionName,
                        self::GUARD_NAME
                    )
            );

        $permissionMelihat = $semuaPermissionUser
            ->filter(
                fn (Permission $permission): bool =>
                    in_array(
                        $permission->name,
                        self::VIEW_PERMISSIONS,
                        true
                    )
            )
            ->values();

        $permissionMengelola = $semuaPermissionUser
            ->filter(
                fn (Permission $permission): bool =>
                    in_array(
                        $permission->name,
                        self::MANAGE_PERMISSIONS,
                        true
                    )
            )
            ->values();

        $superAdmin = Role::findOrCreate(
            'super_admin',
            self::GUARD_NAME
        );

        $superAdmin->givePermissionTo(
            $semuaPermissionUser
        );

        $petugas = Role::findOrCreate(
            'petugas',
            self::GUARD_NAME
        );

        $petugas->revokePermissionTo(
            $permissionMengelola
        );

        $petugas->givePermissionTo(
            $permissionMelihat
        );

        foreach ([
            'donor',
            'pendonor',
            'pemohon_donor',
            'pemohon-donor',
            'rumah_sakit',
        ] as $roleName) {
            $role = Role::query()
                ->where(
                    'guard_name',
                    self::GUARD_NAME
                )
                ->where(
                    'name',
                    $roleName
                )
                ->first();

            if (! $role instanceof Role) {
                continue;
            }

            $role->revokePermissionTo(
                $semuaPermissionUser
            );
        }

        $permissionRegistrar
            ->forgetCachedPermissions();
    }
}