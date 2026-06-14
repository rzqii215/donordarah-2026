<?php

namespace App\Filament\Admin\Resources\ItemPermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\ItemPermintaanDarahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemPermintaanDarah extends EditRecord
{
    protected static string $resource = ItemPermintaanDarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
