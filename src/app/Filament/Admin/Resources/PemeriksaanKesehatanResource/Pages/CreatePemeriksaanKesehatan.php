<?php

namespace App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;

use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\PemeriksaanKesehatanResource;
use App\Models\PendaftaranDonor;
use App\Services\LayananPemeriksaanKesehatan;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePemeriksaanKesehatan extends CreateRecord
{
    protected static string $resource =
        PemeriksaanKesehatanResource::class;

    protected function afterFill(): void
    {
        $pendaftaranDonorId = request()->integer(
            'pendaftaran_donor_id'
        );

        if ($pendaftaranDonorId <= 0) {
            return;
        }

        $pendaftaran = PendaftaranDonor::query()
            ->where(
                'status',
                StatusPendaftaranDonor::Hadir->value
            )
            ->whereDoesntHave('pemeriksaanKesehatan')
            ->find($pendaftaranDonorId);

        if ($pendaftaran === null) {
            return;
        }

        $this->data['pendaftaran_donor_id'] =
            $pendaftaran->id;
    }

    protected function handleRecordCreation(
        array $data
    ): Model {
        $pendaftaranDonorId = (int) (
            $data['pendaftaran_donor_id'] ?? 0
        );

        if ($pendaftaranDonorId <= 0) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Pendaftaran donor wajib dipilih.',
            ]);
        }

        $pendaftaran = PendaftaranDonor::query()
            ->where(
                'status',
                StatusPendaftaranDonor::Hadir->value
            )
            ->whereDoesntHave('pemeriksaanKesehatan')
            ->find($pendaftaranDonorId);

        if ($pendaftaran === null) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Pemeriksaan kesehatan hanya dapat dibuat dari pendaftaran donor berstatus Hadir dan belum memiliki pemeriksaan kesehatan.',
            ]);
        }

        return app(
            LayananPemeriksaanKesehatan::class
        )->simpan(
            pendaftaran: $pendaftaran,
            petugasId: (int) Filament::auth()->id(),
            data: $data,
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pemeriksaan kesehatan berhasil disimpan.';
    }
}