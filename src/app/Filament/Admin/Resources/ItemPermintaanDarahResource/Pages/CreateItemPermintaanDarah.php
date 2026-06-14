<?php

namespace App\Filament\Admin\Resources\ItemPermintaanDarahResource\Pages;

use App\Filament\Admin\Resources\ItemPermintaanDarahResource;
use App\Models\KantongDarah;
use App\Models\PermintaanDarah;
use App\Services\LayananAlokasiDarah;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateItemPermintaanDarah extends CreateRecord
{
    protected static string $resource =
        ItemPermintaanDarahResource::class;

    protected function handleRecordCreation(
        array $data
    ): Model {
        $permintaan =
            PermintaanDarah::query()
                ->findOrFail(
                    $data['permintaan_darah_id']
                );

        $kantong =
            KantongDarah::query()
                ->findOrFail(
                    $data['kantong_darah_id']
                );

        return app(
            LayananAlokasiDarah::class
        )->alokasikanManual(
            permintaan: $permintaan,
            kantong: $kantong,
            petugasId: (int) Filament
                ::auth()
                ->id(),
            catatan: $data['catatan'] ?? null,
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Kantong darah berhasil dialokasikan.';
    }
}