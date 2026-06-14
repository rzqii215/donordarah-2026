<?php

namespace App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;

use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use App\Models\PendaftaranDonor;
use App\Services\LayananPemeriksaanKesehatan;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPemeriksaanKesehatan extends EditRecord
{
    protected static string $resource =
        PemeriksaanKesehatanResource::class;

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        $pendaftaran =
            PendaftaranDonor::query()
                ->findOrFail(
                    $record
                        ->pendaftaran_donor_id
                );

        return app(
            LayananPemeriksaanKesehatan::class
        )->simpan(
            pendaftaran: $pendaftaran,
            petugasId: (int) Filament
                ::auth()
                ->id(),
            data: array_merge(
                $data,
                [
                    'pendaftaran_donor_id' =>
                        $record
                            ->pendaftaran_donor_id,
                ]
            ),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pemeriksaan kesehatan berhasil diperbarui.';
    }
}