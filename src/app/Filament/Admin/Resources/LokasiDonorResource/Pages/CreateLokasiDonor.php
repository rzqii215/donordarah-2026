<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Pages;

use App\Filament\Admin\Resources\LokasiDonorResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateLokasiDonor extends CreateRecord
{
    protected static string $resource =
        LokasiDonorResource::class;

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {
        $data['dibuat_oleh'] = Filament::auth()->id();

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Lokasi donor berhasil ditambahkan.';
    }
}