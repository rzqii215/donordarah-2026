<?php

namespace App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;

use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use App\Models\PemeriksaanKesehatan;
use App\Models\PendaftaranDonor;
use App\Services\LayananPemeriksaanKesehatan;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePemeriksaanKesehatan extends CreateRecord
{
    protected static string $resource =
        PemeriksaanKesehatanResource::class;

    protected function handleRecordCreation(
        array $data
    ): Model {
        $pendaftaran =
            PendaftaranDonor::query()
                ->findOrFail(
                    $data['pendaftaran_donor_id']
                );

        return app(
            LayananPemeriksaanKesehatan::class
        )->simpan(
            pendaftaran: $pendaftaran,
            petugasId: (int) Filament
                ::auth()
                ->id(),
            data: $data,
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pemeriksaan kesehatan berhasil disimpan.';
    }
}