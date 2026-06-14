<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Pages;

use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Services\LayananDistribusiDarah;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDistribusiDarah extends EditRecord
{
    protected static string $resource =
        DistribusiDarahResource::class;

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        return app(
            LayananDistribusiDarah::class
        )->perbarui(
            distribusi: $record,
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
        return 'Jadwal distribusi berhasil diperbarui.';
    }
}