<?php

namespace App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;

use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use App\Models\PemeriksaanKesehatan;
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

    protected function authorizeAccess(): void
    {
        abort_unless(
            $this->record instanceof PemeriksaanKesehatan
                && $this->bolehDiubah($this->record),
            403
        );
    }

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        abort_unless(
            $record instanceof PemeriksaanKesehatan,
            404
        );

        $pendaftaran =
            PendaftaranDonor::query()
                ->findOrFail(
                    $record->pendaftaran_donor_id
                );

        return app(
            LayananPemeriksaanKesehatan::class
        )->simpan(
            pendaftaran: $pendaftaran,
            petugasId: (int) Filament::auth()->id(),
            data: array_merge(
                $data,
                [
                    'pendaftaran_donor_id' =>
                        $record->pendaftaran_donor_id,
                ]
            ),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Pemeriksaan'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pemeriksaan kesehatan berhasil diperbarui.';
    }

    private function bolehDiubah(
        PemeriksaanKesehatan $record
    ): bool {
        $pendaftaran = $record->pendaftaran;

        if ($pendaftaran === null) {
            return false;
        }

        if ($pendaftaran->kantongDarah()->exists()) {
            return false;
        }

        $status =
            $pendaftaran->status instanceof StatusPendaftaranDonor
                ? $pendaftaran->status
                : StatusPendaftaranDonor::from(
                    (string) $pendaftaran->status
                );

        return $status === StatusPendaftaranDonor::Hadir;
    }
}