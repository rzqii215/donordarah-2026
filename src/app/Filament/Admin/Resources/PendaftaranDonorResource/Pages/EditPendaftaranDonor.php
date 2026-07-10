<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Models\PendaftaranDonor;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranDonor extends EditRecord
{
    protected static string $resource = PendaftaranDonorResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(
            $this->record instanceof PendaftaranDonor
                && PendaftaranDonorResource::canEdit($this->record),
            403
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Pendaftaran'),
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
        return 'Pendaftaran donor berhasil diperbarui.';
    }
}