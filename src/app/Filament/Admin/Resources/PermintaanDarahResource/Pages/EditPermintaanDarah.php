<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Models\PermintaanDarah;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermintaanDarah extends EditRecord
{
    protected static string $resource = PermintaanDarahResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(
            $this->record instanceof PermintaanDarah
                && $this->record->dapatDiubah(),
            403
        );
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $status = $data['status'] ?? null;

        if ($status instanceof StatusPermintaanDarah) {
            $status = $status->value;
        }

        if (
            ! in_array(
                $status,
                [
                    StatusPermintaanDarah::Ditolak->value,
                    StatusPermintaanDarah::Dibatalkan->value,
                ],
                true
            )
        ) {
            $data['alasan_penolakan'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Pengajuan'),
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
        return 'Pengajuan Kebutuhan Donor berhasil diperbarui.';
    }
}