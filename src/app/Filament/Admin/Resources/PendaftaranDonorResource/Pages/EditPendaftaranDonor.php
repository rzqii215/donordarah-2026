<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranDonor extends EditRecord
{
    protected static string $resource =
        PendaftaranDonorResource::class;

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
        return 'Pendaftaran donor berhasil diperbarui.';
    }
}