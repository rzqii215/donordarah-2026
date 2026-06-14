<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Pages;

use App\Filament\Admin\Resources\JadwalDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJadwalDonors extends ListRecords
{
    protected static string $resource =
        JadwalDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Jadwal Donor')
                ->icon('heroicon-o-plus'),
        ];
    }
}