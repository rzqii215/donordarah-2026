<?php

namespace App\Enums;

enum StatusKelayakanDonor: string
{
    case Layak = 'eligible';
    case TidakLayak = 'ineligible';

    public function label(): string
    {
        return match ($this) {
            self::Layak => 'Layak Donor',
            self::TidakLayak => 'Tidak Layak Donor',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Layak => 'success',
            self::TidakLayak => 'danger',
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