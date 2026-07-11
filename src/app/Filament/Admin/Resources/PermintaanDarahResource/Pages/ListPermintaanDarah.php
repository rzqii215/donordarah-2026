<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermintaanDarah extends ListRecords
{
    protected static string $resource = PermintaanDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pengajuan'),
        ];
    }
}