<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProfilRumahSakit extends ViewRecord
{
    protected static string $resource = ProfilRumahSakitResource::class;

    public function getTitle(): string
    {
        return 'Detail Pemohon Donor';
    }

    public function getHeading(): string
    {
        return 'Detail Pemohon Donor';
    }

    public function getSubheading(): ?string
    {
        return 'Informasi lengkap pihak pemohon donor, status verifikasi, dan data penanggung jawab.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah Data')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}