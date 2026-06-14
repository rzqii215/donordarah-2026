<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Pages;

use App\Filament\Admin\Resources\ProfilPendonorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfilPendonor extends CreateRecord
{
    protected static string $resource =
        ProfilPendonorResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Profil Pendonor berhasil ditambahkan.';
    }
}