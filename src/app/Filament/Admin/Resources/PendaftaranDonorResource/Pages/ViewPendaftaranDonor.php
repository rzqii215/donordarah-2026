<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;

use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\PendaftaranDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPendaftaranDonor extends ViewRecord
{
    protected static string $resource =
        PendaftaranDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Data')
                ->icon('heroicon-o-pencil-square')
                ->visible(
                    fn (): bool => $this->record->status ===
                        StatusPendaftaranDonor::Menunggu
                ),
        ];
    }
}