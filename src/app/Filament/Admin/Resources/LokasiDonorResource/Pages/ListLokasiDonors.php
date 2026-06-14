<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Pages;

use App\Filament\Admin\Resources\LokasiDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLokasiDonors extends ListRecords
{
    protected static string $resource =
        LokasiDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Lokasi Donor')
                ->icon('heroicon-o-plus'),
        ];
    }
}