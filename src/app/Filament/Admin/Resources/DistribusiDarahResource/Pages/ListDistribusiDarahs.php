<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Pages;

use App\Filament\Admin\Resources\DistribusiDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistribusiDarahs extends ListRecords
{
    protected static string $resource =
        DistribusiDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Distribusi')
                ->icon('heroicon-o-plus'),
        ];
    }
}