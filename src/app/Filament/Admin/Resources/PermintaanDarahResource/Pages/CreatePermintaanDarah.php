<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Models\ProfilRumahSakit;
use App\Services\LayananPermintaanDarah;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePermintaanDarah extends CreateRecord
{
    protected static string $resource =
        PermintaanDarahResource::class;

    protected function handleRecordCreation(
        array $data
    ): Model {
        $rumahSakit =
            ProfilRumahSakit::query()
                ->findOrFail(
                    $data['profil_rumah_sakit_id']
                );

        return app(
            LayananPermintaanDarah::class
        )->buat(
            rumahSakit: $rumahSakit,
            data: $data,
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Permintaan darah berhasil ditambahkan.';
    }
}