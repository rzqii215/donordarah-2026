<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Pages;

use App\Filament\Admin\Resources\LokasiDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLokasiDonor extends ViewRecord
{
    protected static string $resource =
        LokasiDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Lokasi')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}