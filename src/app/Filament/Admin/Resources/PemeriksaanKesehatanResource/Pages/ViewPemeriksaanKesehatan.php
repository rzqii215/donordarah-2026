<?php

namespace App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;

use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPemeriksaanKesehatan extends ViewRecord
{
    protected static string $resource =
        PemeriksaanKesehatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Pemeriksaan')
                ->icon(
                    'heroicon-o-pencil-square'
                ),
        ];
    }
}