<?php

namespace App\Enums;

enum StatusVerifikasiRumahSakit: string
{
    case Menunggu = 'pending';
    case Disetujui = 'approved';
    case Ditolak = 'rejected';
    case Ditangguhkan = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Verifikasi',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::Ditangguhkan => 'Ditangguhkan',
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