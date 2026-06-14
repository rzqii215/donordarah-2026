<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Pages;

use App\Enums\StatusJadwalDonor;
use App\Filament\Admin\Resources\JadwalDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJadwalDonor extends EditRecord
{
    protected static string $resource =
        JadwalDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye'),

            Actions\DeleteAction::make()
                ->label('Hapus')
                ->visible(
                    fn (): bool => $this->record->status ===
                        StatusJadwalDonor::Draf
                )
                ->requiresConfirmation(),

            Actions\RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Jadwal donor berhasil diperbarui.';
    }
}