<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfilRumahSakit extends EditRecord
{
    protected static string $resource = ProfilRumahSakitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profil Rumah Sakit berhasil diperbarui.';
    }
}