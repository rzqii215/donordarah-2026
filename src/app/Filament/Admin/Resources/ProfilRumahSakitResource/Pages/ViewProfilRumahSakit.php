<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProfilRumahSakit extends ViewRecord
{
    protected static string $resource = ProfilRumahSakitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Data')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}