<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftaranDonors extends ListRecords
{
    protected static string $resource =
        PendaftaranDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pendaftaran')
                ->icon('heroicon-o-plus'),
        ];
    }
}