<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Pages;

use App\Filament\Admin\Resources\KantongDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKantongDarah extends ViewRecord
{
    protected static string $resource = KantongDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Kantong Darah')
                ->visible(function (): bool {
                    return KantongDarahResource::canEdit($this->record);
                }),
        ];
    }
}