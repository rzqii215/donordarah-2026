<?php

namespace App\Enums;

enum PeranPengguna: string
{
    case SuperAdmin = 'super_admin';
    case Petugas = 'petugas';
    case Pendonor = 'donor';
    case RumahSakit = 'hospital';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Petugas => 'Petugas',
            self::Pendonor => 'Pendonor',
            self::RumahSakit => 'Rumah Sakit',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $peran) {
            $options[$peran->value] = $peran->label();
        }

        return $options;
    }
}