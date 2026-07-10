<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProfilRumahSakit extends CreateRecord
{
    protected static string $resource = ProfilRumahSakitResource::class;

    public function getTitle(): string
    {
        return 'Tambah Pemohon Donor';
    }

    public function getHeading(): string
    {
        return 'Tambah Pemohon Donor';
    }

    public function getSubheading(): ?string
    {
        return 'Tambahkan profil yayasan, komunitas, instansi, organisasi, atau pihak umum yang membutuhkan donor darah.';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status_verifikasi'] ??= StatusVerifikasiRumahSakit::Menunggu->value;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        if ($record->pengguna) {
            $record->pengguna->syncRoles([
                PeranPengguna::PemohonDonor->value,
            ]);

            if ($record->pengguna->status !== StatusPengguna::Aktif) {
                $record->pengguna->update([
                    'status' => StatusPengguna::Aktif,
                ]);
            }
        }

        return $record;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Profil Pemohon Donor berhasil ditambahkan.';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}