<?php

namespace App\Enums;

enum JenisKelamin: string
{
    case LakiLaki = 'male';
    case Perempuan = 'female';

    public function label(): string
    {
        return match ($this) {
            self::LakiLaki => 'Laki-laki',
            self::Perempuan => 'Perempuan',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $jenisKelamin) {
            $options[$jenisKelamin->value] = $jenisKelamin->label();
        }

        return $options;
    }
}