<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Pages;

use App\Enums\StatusDistribusiDarah;
use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Models\DistribusiDarah;
use App\Services\LayananDistribusiDarah;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDistribusiDarah extends EditRecord
{
    protected static string $resource = DistribusiDarahResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(
            $this->record instanceof DistribusiDarah
                && $this->statusValue($this->record) === StatusDistribusiDarah::Dijadwalkan->value,
            403
        );
    }

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        /** @var DistribusiDarah $record */
        return app(
            LayananDistribusiDarah::class
        )->perbarui(
            distribusi: $record,
            data: $data
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Distribusi'),
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
        return 'Jadwal distribusi berhasil diperbarui.';
    }

    private function statusValue(DistribusiDarah $record): string
    {
        return $record->status instanceof StatusDistribusiDarah
            ? $record->status->value
            : (string) $record->status;
    }
}