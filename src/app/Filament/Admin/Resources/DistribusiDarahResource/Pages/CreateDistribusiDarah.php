<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Pages;

use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Models\PermintaanDarah;
use App\Services\LayananDistribusiDarah;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDistribusiDarah extends CreateRecord
{
    protected static string $resource =
        DistribusiDarahResource::class;

    protected function handleRecordCreation(
        array $data
    ): Model {
        $permintaan =
            PermintaanDarah::query()
                ->findOrFail(
                    $data['permintaan_darah_id']
                );

        return app(
            LayananDistribusiDarah::class
        )->buat(
            permintaan: $permintaan,
            petugasId: (int) Filament
                ::auth()
                ->id(),
            data: $data,
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Distribusi darah berhasil dijadwalkan.';
    }
}