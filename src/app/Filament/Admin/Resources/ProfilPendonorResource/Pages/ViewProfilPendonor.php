<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Pages;

use App\Filament\Admin\Resources\ProfilPendonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProfilPendonor extends ViewRecord
{
    protected static string $resource =
        ProfilPendonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Data')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}