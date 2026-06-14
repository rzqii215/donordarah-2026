<?php

namespace App\Enums;

enum JenisKomponenDarah: string
{
    case DarahUtuh = 'whole_blood';

    public function label(): string
    {
        return match ($this) {
            self::DarahUtuh => 'Darah Utuh',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $jenis) {
            $options[$jenis->value] = $jenis->label();
        }

        return $options;
    }
}