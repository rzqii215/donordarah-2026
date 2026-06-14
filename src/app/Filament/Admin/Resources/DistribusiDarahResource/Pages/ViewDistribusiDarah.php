<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Pages;

use App\Filament\Admin\Resources\DistribusiDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDistribusiDarah extends ViewRecord
{
    protected static string $resource =
        DistribusiDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Jadwal')
                ->icon('heroicon-o-pencil-square')
                ->visible(
                    fn (): bool => $this
                        ->record
                        ->dapatDiubah()
                ),
        ];
    }
}