<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;

use App\Enums\StatusJadwalDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Models\JadwalDonor;
use App\Models\PendaftaranDonor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePendaftaranDonor extends CreateRecord
{
    protected static string $resource =
        PendaftaranDonorResource::class;

    protected function handleRecordCreation(
        array $data
    ): Model {
        return DB::transaction(function () use (
            $data
        ): PendaftaranDonor {
            $jadwalDonorId = (int) (
                $data['jadwal_donor_id'] ?? 0
            );

            $pendonorId = (int) (
                $data['pendonor_id'] ?? 0
            );

            if ($jadwalDonorId <= 0) {
                throw ValidationException::withMessages([
                    'jadwal_donor_id' =>
                        'Jadwal donor wajib dipilih.',
                ]);
            }

            if ($pendonorId <= 0) {
                throw ValidationException::withMessages([
                    'pendonor_id' =>
                        'Pendonor wajib dipilih.',
                ]);
            }

            $jadwal = JadwalDonor::query()
                ->lockForUpdate()
                ->findOrFail($jadwalDonorId);

            $this->pastikanJadwalMasihBisaDidaftarkan(
                jadwal: $jadwal
            );

            $this->pastikanBelumTerdaftarDiJadwalYangSama(
                jadwalDonorId: $jadwalDonorId,
                pendonorId: $pendonorId
            );

            $pendaftaran = new PendaftaranDonor();

            $pendaftaran
                ->forceFill([
                    'nomor_pendaftaran' =>
                        $this->buatNomorPendaftaran(),

                    'jadwal_donor_id' =>
                        $jadwalDonorId,

                    'pendonor_id' =>
                        $pendonorId,

                    'status' =>
                        StatusPendaftaranDonor::Menunggu->value,

                    'jawaban_skrining' =>
                        $data['jawaban_skrining'] ?? [],

                    'catatan' =>
                        $data['catatan'] ?? null,

                    'peninjau_id' =>
                        null,

                    'ditinjau_pada' =>
                        null,

                    'hadir_pada' =>
                        null,

                    'selesai_pada' =>
                        null,

                    'dibatalkan_pada' =>
                        null,

                    'alasan_penolakan' =>
                        null,

                    'alasan_pembatalan' =>
                        null,
                ])
                ->save();

            return $pendaftaran;
        });
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pendaftaran donor berhasil dibuat.';
    }

    private function pastikanJadwalMasihBisaDidaftarkan(
        JadwalDonor $jadwal
    ): void {
        $status = $jadwal->status instanceof StatusJadwalDonor
            ? $jadwal->status->value
            : (string) $jadwal->status;

        if ($status !== StatusJadwalDonor::Dipublikasikan->value) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Jadwal donor belum dipublikasikan.',
            ]);
        }

        if (
            $jadwal->pendaftaran_dibuka_pada !== null
            && now()->lessThan(
                $jadwal->pendaftaran_dibuka_pada
            )
        ) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Pendaftaran untuk jadwal donor ini belum dibuka.',
            ]);
        }

        if (
            $jadwal->pendaftaran_ditutup_pada !== null
            && now()->greaterThan(
                $jadwal->pendaftaran_ditutup_pada
            )
        ) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Pendaftaran untuk jadwal donor ini sudah ditutup.',
            ]);
        }
    }

    private function pastikanBelumTerdaftarDiJadwalYangSama(
        int $jadwalDonorId,
        int $pendonorId
    ): void {
        $sudahTerdaftar = PendaftaranDonor::query()
            ->where(
                'jadwal_donor_id',
                $jadwalDonorId
            )
            ->where(
                'pendonor_id',
                $pendonorId
            )
            ->exists();

        if ($sudahTerdaftar) {
            throw ValidationException::withMessages([
                'pendonor_id' =>
                    'Pendonor ini sudah terdaftar pada jadwal donor yang sama.',
            ]);
        }
    }

    private function buatNomorPendaftaran(): string
    {
        $tanggal = now()->format('Ymd');

        $nomorUrut = PendaftaranDonor::query()
            ->whereDate(
                'created_at',
                now()->toDateString()
            )
            ->lockForUpdate()
            ->count() + 1;

        do {
            $nomorPendaftaran =
                'REG-'
                . $tanggal
                . '-'
                . str_pad(
                    (string) $nomorUrut,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $sudahAda = PendaftaranDonor::query()
                ->where(
                    'nomor_pendaftaran',
                    $nomorPendaftaran
                )
                ->exists();

            $nomorUrut++;
        } while ($sudahAda);

        return $nomorPendaftaran;
    }
}