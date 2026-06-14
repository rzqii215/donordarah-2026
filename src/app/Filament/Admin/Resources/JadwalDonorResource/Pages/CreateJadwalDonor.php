<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Pages;

use App\Enums\StatusJadwalDonor;
use App\Filament\Admin\Resources\JadwalDonorResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalDonor extends CreateRecord
{
    protected static string $resource =
        JadwalDonorResource::class;

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {
        $data['dibuat_oleh'] =
            Filament::auth()->id();

        $data['status'] =
            StatusJadwalDonor::Draf->value;

        $data['dipublikasikan_pada'] = null;
        $data['dibatalkan_pada'] = null;
        $data['alasan_pembatalan'] = null;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Jadwal donor berhasil ditambahkan.';
    }
}