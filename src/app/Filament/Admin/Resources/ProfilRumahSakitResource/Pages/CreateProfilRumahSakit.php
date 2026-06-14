<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;

use App\Enums\StatusPengguna;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use App\Models\ProfilRumahSakit;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateProfilRumahSakit extends CreateRecord
{
    protected static string $resource = ProfilRumahSakitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status_verifikasi'] = StatusVerifikasiRumahSakit::Menunggu->value;
        $data['diverifikasi_oleh'] = null;
        $data['diverifikasi_pada'] = null;
        $data['alasan_penolakan'] = null;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): ProfilRumahSakit {
            $profil = ProfilRumahSakit::create($data);

            $profil->pengguna()->update([
                'status' => StatusPengguna::Menunggu,
            ]);

            return $profil;
        });
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Profil Rumah Sakit berhasil ditambahkan.';
    }
}