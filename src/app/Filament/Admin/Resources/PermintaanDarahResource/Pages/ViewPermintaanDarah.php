<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermintaanDarah extends ViewRecord
{
    protected static string $resource =
        PermintaanDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Data')
                ->icon('heroicon-o-pencil-square')
                ->visible(
                    fn (): bool => $this
                        ->record
                        ->dapatDiubah()
                ),
        ];
    }
}