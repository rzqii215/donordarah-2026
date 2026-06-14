<?php

namespace App\Enums;

enum GolonganDarah: string
{
    case A = 'A';
    case B = 'B';
    case AB = 'AB';
    case O = 'O';

    public function label(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $golonganDarah) {
            $options[$golonganDarah->value] = $golonganDarah->label();
        }

        return $options;
    }
}