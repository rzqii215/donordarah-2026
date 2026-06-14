<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Pages;

use App\Enums\StatusKantongDarah;
use App\Filament\Admin\Resources\KantongDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKantongDarah extends EditRecord
{
    protected static string $resource =
        KantongDarahResource::class;

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
                        StatusKantongDarah::Menunggu
                )
                ->requiresConfirmation(),

            Actions\RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Kantong darah berhasil diperbarui.';
    }
}