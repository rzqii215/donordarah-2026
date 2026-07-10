<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Pages;

use App\Enums\StatusDistribusiDarah;
use App\Filament\Admin\Resources\DistribusiDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDistribusiDarah extends ViewRecord
{
    protected static string $resource = DistribusiDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Distribusi')
                ->visible(function (): bool {
                    $status = $this->record->status instanceof StatusDistribusiDarah
                        ? $this->record->status->value
                        : (string) $this->record->status;

                    return $status === StatusDistribusiDarah::Dijadwalkan->value;
                }),
        ];
    }
}