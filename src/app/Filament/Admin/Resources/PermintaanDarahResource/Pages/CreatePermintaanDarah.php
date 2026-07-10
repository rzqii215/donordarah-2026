<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Pages;

use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Models\PermintaanDarah;
use Filament\Resources\Pages\CreateRecord;

class CreatePermintaanDarah extends CreateRecord
{
    protected static string $resource = PermintaanDarahResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nomor_permintaan'] = $data['nomor_permintaan']
            ?? $this->buatNomorPermintaan();

        $data['status'] = $data['status']
            ?? StatusPermintaanDarah::Diajukan->value;

        if (
            ! in_array(
                $data['status'],
                [
                    StatusPermintaanDarah::Ditolak->value,
                    StatusPermintaanDarah::Dibatalkan->value,
                ],
                true
            )
        ) {
            $data['alasan_penolakan'] = null;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengajuan Kebutuhan Donor berhasil dibuat.';
    }

    private function buatNomorPermintaan(): string
    {
        $tanggal = now()->format('Ymd');

        $nomorUrut = PermintaanDarah::query()
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        do {
            $nomorPermintaan = 'PGJ-' . $tanggal . '-' . str_pad(
                (string) $nomorUrut,
                4,
                '0',
                STR_PAD_LEFT
            );

            $sudahAda = PermintaanDarah::query()
                ->where('nomor_permintaan', $nomorPermintaan)
                ->exists();

            $nomorUrut++;
        } while ($sudahAda);

        return $nomorPermintaan;
    }
}   