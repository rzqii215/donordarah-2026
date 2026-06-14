<?php

namespace App\Enums;

enum StatusPengguna: string
{
    case Menunggu = 'pending';
    case Aktif = 'active';
    case TidakAktif = 'inactive';
    case Ditangguhkan = 'suspended';
    case Ditolak = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Verifikasi',
            self::Aktif => 'Aktif',
            self::TidakAktif => 'Tidak Aktif',
            self::Ditangguhkan => 'Ditangguhkan',
            self::Ditolak => 'Ditolak',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }
}