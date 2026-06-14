<?php

namespace App\Enums;

enum StatusDistribusiDarah: string
{
    case Dijadwalkan = 'scheduled';
    case SiapDiserahkan = 'ready';
    case Selesai = 'completed';
    case Dibatalkan = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Dijadwalkan => 'Dijadwalkan',
            self::SiapDiserahkan => 'Siap Diserahkan',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Dijadwalkan => 'warning',
            self::SiapDiserahkan => 'info',
            self::Selesai => 'success',
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