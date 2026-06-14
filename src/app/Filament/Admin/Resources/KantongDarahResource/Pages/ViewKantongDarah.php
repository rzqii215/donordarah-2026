<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Pages;

use App\Enums\StatusKantongDarah;
use App\Filament\Admin\Resources\KantongDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKantongDarah extends ViewRecord
{
    protected static string $resource =
        KantongDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Data')
                ->icon('heroicon-o-pencil-square')
                ->visible(
                    fn (): bool => ! in_array(
                        $this->record->status,
                        [
                            StatusKantongDarah::Dipesan,
                            StatusKantongDarah
                                ::Didistribusikan,
                        ],
                        true
                    )
                ),
        ];
    }
}