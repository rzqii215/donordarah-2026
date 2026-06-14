<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Models\PendaftaranDonor;
use App\Services\LayananPendaftaranDonor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePendaftaranDonor extends CreateRecord
{
    protected static string $resource =
        PendaftaranDonorResource::class;

    protected function handleRecordCreation(
        array $data
    ): Model {
        return app(
            LayananPendaftaranDonor::class
        )->daftar(
            jadwalDonorId:
                (int) $data['jadwal_donor_id'],

            pendonorId:
                (int) $data['pendonor_id'],

            data: [
                'jawaban_skrining' =>
                    $data['jawaban_skrining'] ?? null,

                'catatan' =>
                    $data['catatan'] ?? null,
            ],
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pendaftaran donor berhasil ditambahkan.';
    }
}