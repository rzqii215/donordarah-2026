<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Pages;

use App\Filament\Admin\Resources\ProfilPendonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfilPendonors extends ListRecords
{
    protected static string $resource =
        ProfilPendonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Profil Pendonor')
                ->icon('heroicon-o-plus'),
        ];
    }
}