<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Pages;

use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\KantongDarahResource;
use App\Models\PendaftaranDonor;
use App\Services\LayananKantongDarah;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateKantongDarah extends CreateRecord
{
    protected static string $resource =
        KantongDarahResource::class;

    protected function afterFill(): void
    {
        $pendaftaranDonorId = request()->integer(
            'pendaftaran_donor_id'
        );

        if ($pendaftaranDonorId <= 0) {
            return;
        }

        $pendaftaran = PendaftaranDonor::query()
            ->with([
                'pemeriksaanKesehatan',
                'kantongDarah',
            ])
            ->where(
                'status',
                StatusPendaftaranDonor::Layak->value
            )
            ->whereDoesntHave('kantongDarah')
            ->find($pendaftaranDonorId);

        if ($pendaftaran === null) {
            return;
        }

        $this->data['pendaftaran_donor_id'] =
            $pendaftaran->id;

        if (
            $pendaftaran
                ->pemeriksaanKesehatan
                ?->golongan_darah !== null
        ) {
            $this->data['golongan_darah'] =
                $this->enumValue(
                    $pendaftaran
                        ->pemeriksaanKesehatan
                        ->golongan_darah
                );
        }

        if (
            $pendaftaran
                ->pemeriksaanKesehatan
                ?->rhesus !== null
        ) {
            $this->data['rhesus'] =
                $this->enumValue(
                    $pendaftaran
                        ->pemeriksaanKesehatan
                        ->rhesus
                );
        }
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
            ->with([
                'pemeriksaanKesehatan',
                'kantongDarah',
            ])
            ->where(
                'status',
                StatusPendaftaranDonor::Layak->value
            )
            ->whereDoesntHave('kantongDarah')
            ->find($pendaftaranDonorId);

        if ($pendaftaran === null) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Kantong darah hanya dapat dibuat dari pendaftaran donor berstatus Layak Donor dan belum memiliki kantong darah.',
            ]);
        }

        return app(
            LayananKantongDarah::class
        )->buat(
            pendaftaran: $pendaftaran,
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
        return 'Kantong darah berhasil ditambahkan.';
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}