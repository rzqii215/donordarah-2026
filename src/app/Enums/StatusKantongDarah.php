<?php

namespace App\Enums;

enum StatusKantongDarah: string
{
    case Menunggu = 'pending';
    case Tersedia = 'available';
    case Dipesan = 'reserved';
    case Didistribusikan = 'distributed';
    case Kedaluwarsa = 'expired';
    case Rusak = 'damaged';
    case Ditolak = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Verifikasi',
            self::Tersedia => 'Tersedia',
            self::Dipesan => 'Dialokasikan',
            self::Didistribusikan => 'Didistribusikan',
            self::Kedaluwarsa => 'Kedaluwarsa',
            self::Rusak => 'Rusak',
            self::Ditolak => 'Ditolak',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Tersedia => 'success',
            self::Dipesan => 'info',
            self::Didistribusikan => 'primary',
            self::Kedaluwarsa => 'gray',
            self::Rusak => 'danger',
            self::Ditolak => 'danger',
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