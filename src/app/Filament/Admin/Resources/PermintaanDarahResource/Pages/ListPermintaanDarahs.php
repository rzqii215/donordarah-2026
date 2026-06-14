<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermintaanDarahs extends ListRecords
{
    protected static string $resource =
        PermintaanDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Permintaan Darah')
                ->icon('heroicon-o-plus'),
        ];
    }
}