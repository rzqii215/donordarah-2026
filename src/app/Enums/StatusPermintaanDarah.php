<?php

namespace App\Enums;

enum StatusPermintaanDarah: string
{
    case Draf = 'draft';
    case Diajukan = 'submitted';
    case Ditinjau = 'under_review';
    case MenungguStok = 'waiting_for_stock';
    case Disetujui = 'approved';
    case SiapDiambil = 'ready_for_pickup';
    case Selesai = 'completed';
    case Ditolak = 'rejected';
    case Dibatalkan = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Diajukan => 'Diajukan',
            self::Ditinjau => 'Sedang Ditinjau',
            self::MenungguStok => 'Menunggu Stok',
            self::Disetujui => 'Disetujui',
            self::SiapDiambil => 'Siap Diambil',
            self::Selesai => 'Selesai',
            self::Ditolak => 'Ditolak',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Draf => 'gray',
            self::Diajukan => 'warning',
            self::Ditinjau => 'info',
            self::MenungguStok => 'warning',
            self::Disetujui => 'success',
            self::SiapDiambil => 'primary',
            self::Selesai => 'success',
            self::Ditolak => 'danger',
            self::Dibatalkan => 'gray',
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