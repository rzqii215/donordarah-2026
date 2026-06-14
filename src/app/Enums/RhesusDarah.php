<?php

namespace App\Enums;

enum RhesusDarah: string
{
    case Positif = 'positive';
    case Negatif = 'negative';

    public function label(): string
    {
        return match ($this) {
            self::Positif => 'Positif (+)',
            self::Negatif => 'Negatif (-)',
        };
    }

    public function simbol(): string
    {
        return match ($this) {
            self::Positif => '+',
            self::Negatif => '-',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $rhesus) {
            $options[$rhesus->value] = $rhesus->label();
        }

        return $options;
    }
}