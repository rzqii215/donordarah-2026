<?php

namespace App\Enums;

enum PeranPengguna: string
{
    case SuperAdmin = 'super_admin';
    case Petugas = 'petugas';
    case Pendonor = 'donor';
    case PemohonDonor = 'pemohon_donor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Petugas => 'Petugas',
            self::Pendonor => 'Pendonor',
            self::PemohonDonor => 'Pemohon Donor',
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