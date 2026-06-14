<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Pages;

use App\Filament\Admin\Resources\LokasiDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLokasiDonor extends EditRecord
{
    protected static string $resource =
        LokasiDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye'),

            Actions\DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation(),

            Actions\RestoreAction::make()
                ->label('Pulihkan'),

            Actions\ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->requiresConfirmation(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Lokasi donor berhasil diperbarui.';
    }
}