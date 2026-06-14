<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfilRumahSakits extends ListRecords
{
    protected static string $resource = ProfilRumahSakitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Rumah Sakit')
                ->icon('heroicon-o-plus'),
        ];
    }
}