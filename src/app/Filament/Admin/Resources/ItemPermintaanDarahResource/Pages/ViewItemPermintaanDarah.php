<?php

namespace App\Filament\Admin\Resources\ItemPermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\ItemPermintaanDarahResource;
use Filament\Resources\Pages\ViewRecord;

class ViewItemPermintaanDarah extends ViewRecord
{
    protected static string $resource = ItemPermintaanDarahResource::class;

    public function getTitle(): string
    {
        return 'Detail Alokasi Kantong Darah';
    }

    public function getHeading(): string
    {
        return 'Detail Alokasi Kantong Darah';
    }

    public function getSubheading(): ?string
    {
        return 'Informasi lengkap alokasi kantong darah, pengajuan kebutuhan donor, status alokasi, dan riwayat pelepasan.';
    }
}