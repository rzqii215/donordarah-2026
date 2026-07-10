<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfilRumahSakits extends ListRecords
{
    protected static string $resource = ProfilRumahSakitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pemohon Donor')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Profil Pemohon Donor';
    }

    public function getHeading(): string
    {
        return 'Profil Pemohon Donor';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola data yayasan, komunitas, instansi, organisasi, atau pihak umum yang mengajukan kebutuhan donor darah.';
    }
}