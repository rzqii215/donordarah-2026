<?php

namespace App\Enums;

enum StatusPendaftaranDonor: string
{
    case Menunggu = 'pending';
    case Disetujui = 'approved';
    case Ditolak = 'rejected';
    case Hadir = 'attended';
    case Layak = 'eligible';
    case TidakLayak = 'ineligible';
    case Selesai = 'completed';
    case Dibatalkan = 'cancelled';
    case TidakHadir = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Verifikasi',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::Hadir => 'Hadir',
            self::Layak => 'Layak Donor',
            self::TidakLayak => 'Tidak Layak Donor',
            self::Selesai => 'Donor Selesai',
            self::Dibatalkan => 'Dibatalkan',
            self::TidakHadir => 'Tidak Hadir',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Disetujui => 'success',
            self::Ditolak => 'danger',
            self::Hadir => 'info',
            self::Layak => 'success',
            self::TidakLayak => 'danger',
            self::Selesai => 'primary',
            self::Dibatalkan => 'gray',
            self::TidakHadir => 'gray',
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

    /**
     * @return array<int, string>
     */
    public static function statusMengurangiKuota(): array
    {
        return [
            self::Menunggu->value,
            self::Disetujui->value,
            self::Hadir->value,
            self::Layak->value,
            self::TidakLayak->value,
            self::Selesai->value,
        ];
    }
}