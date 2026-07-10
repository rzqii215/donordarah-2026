<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Pages;

use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Models\PermintaanDarah;
use App\Services\LayananDistribusiDarah;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDistribusiDarah extends CreateRecord
{
    protected static string $resource = DistribusiDarahResource::class;

    protected function afterFill(): void
    {
        $permintaanDarahId = request()->integer('permintaan_darah_id');

        if ($permintaanDarahId <= 0) {
            return;
        }

        $permintaan = PermintaanDarah::query()
            ->where('status', StatusPermintaanDarah::SiapDiambil->value)
            ->whereDoesntHave('distribusi')
            ->find($permintaanDarahId);

        if ($permintaan === null) {
            return;
        }

        $this->data['permintaan_darah_id'] = $permintaan->id;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $permintaan = PermintaanDarah::query()
            ->findOrFail($data['permintaan_darah_id']);

        return app(LayananDistribusiDarah::class)->buat(
            permintaan: $permintaan,
            petugasId: (int) Filament::auth()->id(),
            data: $data
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Distribusi kantong darah berhasil dibuat.';
    }
}