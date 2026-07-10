<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProfilRumahSakit extends EditRecord
{
    protected static string $resource = ProfilRumahSakitResource::class;

    public function getTitle(): string
    {
        return 'Ubah Pemohon Donor';
    }

    public function getHeading(): string
    {
        return 'Ubah Pemohon Donor';
    }

    public function getSubheading(): ?string
    {
        return 'Perbarui data yayasan, komunitas, instansi, organisasi, atau pihak umum yang membutuhkan donor darah.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye'),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

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

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profil Pemohon Donor berhasil diperbarui.';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}