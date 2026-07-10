<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Filament\Admin\Resources\KantongDarahResource;
use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Models\DistribusiDarah;
use App\Models\KantongDarah;
use App\Models\PendaftaranDonor;
use App\Models\PermintaanDarah;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanOperasional extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stokTersedia = KantongDarah::query()
            ->tersedia()
            ->count();

        $pengajuanAktif = PermintaanDarah::query()
            ->whereIn('status', [
                StatusPermintaanDarah::Diajukan->value,
                StatusPermintaanDarah::Ditinjau->value,
                StatusPermintaanDarah::MenungguStok->value,
                StatusPermintaanDarah::Disetujui->value,
                StatusPermintaanDarah::SiapDiambil->value,
            ])
            ->count();

        $pendaftaranHariIni = PendaftaranDonor::query()
            ->whereDate('created_at', today())
            ->count();

        $distribusiHariIni = DistribusiDarah::query()
            ->where(
                'status',
                StatusDistribusiDarah::Selesai->value
            )
            ->whereDate('diserahkan_pada', today())
            ->count();

        return [
            Stat::make(
                'Stok Darah Tersedia',
                number_format($stokTersedia) . ' kantong'
            )
                ->description(
                    'Lulus mutu dan belum kedaluwarsa'
                )
                ->descriptionIcon(
                    'heroicon-m-check-circle'
                )
                ->color('success')
                ->url(
                    KantongDarahResource::getUrl('index')
                ),

            Stat::make(
                'Pengajuan Aktif',
                number_format($pengajuanAktif)
            )
                ->description(
                    'Pengajuan kebutuhan donor yang masih diproses'
                )
                ->descriptionIcon(
                    'heroicon-m-document-text'
                )
                ->color(
                    $pengajuanAktif > 0
                        ? 'warning'
                        : 'success'
                )
                ->url(
                    PermintaanDarahResource::getUrl('index')
                ),

            Stat::make(
                'Pendaftaran Hari Ini',
                number_format($pendaftaranHariIni)
            )
                ->description(
                    'Pendaftaran donor baru'
                )
                ->descriptionIcon(
                    'heroicon-m-user-plus'
                )
                ->color('info')
                ->url(
                    PendaftaranDonorResource::getUrl('index')
                ),

            Stat::make(
                'Distribusi Hari Ini',
                number_format($distribusiHariIni)
            )
                ->description(
                    'Distribusi yang telah diserahkan'
                )
                ->descriptionIcon(
                    'heroicon-m-truck'
                )
                ->color('primary')
                ->url(
                    DistribusiDarahResource::getUrl('index')
                ),
        ];
    }
}