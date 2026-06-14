<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Pages;

use App\Filament\Admin\Resources\ProfilPendonorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfilPendonor extends EditRecord
{
    protected static string $resource =
        ProfilPendonorResource::class;

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
        return 'Profil Pendonor berhasil diperbarui.';
    }
}