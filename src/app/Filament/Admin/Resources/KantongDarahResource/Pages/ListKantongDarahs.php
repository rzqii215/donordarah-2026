<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Pages;

use App\Filament\Admin\Resources\KantongDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKantongDarahs extends ListRecords
{
    protected static string $resource =
        KantongDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kantong Darah')
                ->icon('heroicon-o-plus'),
        ];
    }
}