<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Pages;

use App\Filament\Admin\Resources\KantongDarahResource;
use App\Models\PendaftaranDonor;
use App\Services\LayananKantongDarah;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateKantongDarah extends CreateRecord
{
    protected static string $resource =
        KantongDarahResource::class;

    protected function handleRecordCreation(
        array $data
    ): Model {
        $pendaftaran =
            PendaftaranDonor::query()
                ->findOrFail(
                    $data['pendaftaran_donor_id']
                );

        return app(
            LayananKantongDarah::class
        )->buat(
            pendaftaran: $pendaftaran,
            data: $data,
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Kantong darah berhasil ditambahkan.';
    }
}