<?php

namespace App\Enums;

enum StatusMutuKantongDarah: string
{
    case Menunggu = 'pending';
    case Lulus = 'passed';
    case Gagal = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Pemeriksaan',
            self::Lulus => 'Lulus Pemeriksaan',
            self::Gagal => 'Tidak Lulus',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Lulus => 'success',
            self::Gagal => 'danger',
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