<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Pages;

use App\Filament\Admin\Resources\KantongDarahResource;
use App\Models\KantongDarah;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKantongDarah extends EditRecord
{
    protected static string $resource = KantongDarahResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(
            $this->record instanceof KantongDarah
                && KantongDarahResource::canEdit($this->record),
            403
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Kantong Darah'),

            Actions\DeleteAction::make()
                ->label('Hapus Kantong Darah')
                ->visible(function (): bool {
                    return $this->record instanceof KantongDarah
                        && KantongDarahResource::canDelete($this->record);
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Kantong darah berhasil diperbarui.';
    }
}