<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Pages;

use App\Enums\StatusJadwalDonor;
use App\Filament\Admin\Resources\JadwalDonorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJadwalDonor extends ViewRecord
{
    protected static string $resource =
        JadwalDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Jadwal')
                ->icon('heroicon-o-pencil-square')
                ->visible(
                    fn (): bool => ! in_array(
                        $this->record->status,
                        [
                            StatusJadwalDonor::Selesai,
                            StatusJadwalDonor::Dibatalkan,
                        ],
                        true
                    )
                ),
        ];
    }
}