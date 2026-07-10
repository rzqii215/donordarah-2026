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
    protected static string $resource = ItemPermintaanDarahResource::class;

    public function getTitle(): string
    {
        return 'Alokasi Manual Kantong Darah';
    }

    public function getHeading(): string
    {
        return 'Alokasi Manual Kantong Darah';
    }

    public function getSubheading(): ?string
    {
        return 'Pilih pengajuan kebutuhan donor dan kantong darah yang sesuai untuk dialokasikan secara manual.';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $permintaan = PermintaanDarah::query()
            ->findOrFail($data['permintaan_darah_id']);

        $kantong = KantongDarah::query()
            ->findOrFail($data['kantong_darah_id']);

        return app(LayananAlokasiDarah::class)->alokasikanManual(
            permintaan: $permintaan,
            kantong: $kantong,
            petugasId: (int) Filament::auth()->id(),
            catatan: $data['catatan'] ?? null,
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Alokasi Kantong Darah berhasil ditambahkan.';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}