<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Services\LayananPermintaanDarah;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPermintaanDarah extends EditRecord
{
    protected static string $resource =
        PermintaanDarahResource::class;

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        return app(
            LayananPermintaanDarah::class
        )->perbarui(
            permintaan: $record,
            data: $data,
        );
    }

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
        return 'Permintaan darah berhasil diperbarui.';
    }
}