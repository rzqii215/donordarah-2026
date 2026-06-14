<?php

namespace App\Enums;

enum TingkatUrgensiPermintaanDarah: string
{
    case Normal = 'normal';
    case Mendesak = 'urgent';
    case Darurat = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Mendesak => 'Mendesak',
            self::Darurat => 'Darurat',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Normal => 'gray',
            self::Mendesak => 'warning',
            self::Darurat => 'danger',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $urgensi) {
            $options[$urgensi->value] = $urgensi->label();
        }

        return $options;
    }
}