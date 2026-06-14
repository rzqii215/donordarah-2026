<?php

namespace App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;

use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPemeriksaanKesehatans extends ListRecords
{
    protected static string $resource =
        PemeriksaanKesehatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pemeriksaan')
                ->icon('heroicon-o-plus'),
        ];
    }
}