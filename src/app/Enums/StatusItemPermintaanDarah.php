<?php

namespace App\Enums;

enum StatusItemPermintaanDarah: string
{
    case Dialokasikan = 'allocated';
    case Dilepaskan = 'released';
    case Didistribusikan = 'distributed';

    public function label(): string
    {
        return match ($this) {
            self::Dialokasikan => 'Dialokasikan',
            self::Dilepaskan => 'Dilepaskan',
            self::Didistribusikan => 'Didistribusikan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Dialokasikan => 'info',
            self::Dilepaskan => 'gray',
            self::Didistribusikan => 'success',
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