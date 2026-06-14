<?php

namespace App\Enums;

enum StatusJadwalDonor: string
{
    case Draf = 'draft';
    case Dipublikasikan = 'published';
    case Berlangsung = 'ongoing';
    case Selesai = 'completed';
    case Dibatalkan = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Dipublikasikan => 'Dipublikasikan',
            self::Berlangsung => 'Berlangsung',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Draf => 'gray',
            self::Dipublikasikan => 'success',
            self::Berlangsung => 'warning',
            self::Selesai => 'info',
            self::Dibatalkan => 'danger',
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