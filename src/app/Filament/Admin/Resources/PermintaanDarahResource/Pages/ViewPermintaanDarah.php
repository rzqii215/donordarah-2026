<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Models\PermintaanDarah;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermintaanDarah extends ViewRecord
{
    protected static string $resource = PermintaanDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Pengajuan')
                ->visible(function (): bool {
                    return $this->record instanceof PermintaanDarah
                        && $this->record->dapatDiubah();
                }),
        ];
    }
}