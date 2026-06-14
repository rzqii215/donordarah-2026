<?php

namespace App\Services;

use App\Enums\StatusKelayakanDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Models\PemeriksaanKesehatan;
use App\Models\PendaftaranDonor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayananPemeriksaanKesehatan
{
    /**
     * @param array<string, mixed> $data
     */
    public function simpan(
        PendaftaranDonor $pendaftaran,
        int $petugasId,
        array $data
    ): PemeriksaanKesehatan {
        return DB::transaction(function () use (
            $pendaftaran,
            $petugasId,
            $data
        ): PemeriksaanKesehatan {
            $pendaftaranTerkunci =
                PendaftaranDonor::query()
                    ->with([
                        'pendonor.profilPendonor',
                        'pemeriksaanKesehatan',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($pendaftaran->id);

            $this->pastikanPendaftaranDapatDiperiksa(
                $pendaftaranTerkunci
            );

            $statusKelayakan =
                $this->normalisasiStatusKelayakan(
                    $data['status_kelayakan']
                );

            $alasanTidakLayak = trim(
                (string) (
                    $data['alasan_tidak_layak']
                    ?? ''
                )
            );

            if (
                $statusKelayakan ===
                    StatusKelayakanDonor::TidakLayak
                && $alasanTidakLayak === ''
            ) {
                throw ValidationException::withMessages([
                    'alasan_tidak_layak' =>
                        'Alasan tidak layak wajib diisi.',
                ]);
            }

            if (
                $statusKelayakan ===
                StatusKelayakanDonor::Layak
            ) {
                $alasanTidakLayak = '';
            }

            $pemeriksaan =
                PemeriksaanKesehatan::query()
                    ->updateOrCreate(
                        [
                            'pendaftaran_donor_id' =>
                                $pendaftaranTerkunci->id,
                        ],
                        [
                            'diperiksa_oleh' =>
                                $petugasId,

                            'berat_badan_kg' =>
                                $data['berat_badan_kg'],

                            'tekanan_sistolik' =>
                                $data['tekanan_sistolik'],

                            'tekanan_diastolik' =>
                                $data['tekanan_diastolik'],

                            'kadar_hemoglobin' =>
                                $data[
                                    'kadar_hemoglobin'
                                ] ?? null,

                            'suhu_tubuh' =>
                                $data['suhu_tubuh']
                                ?? null,

                            'denyut_nadi' =>
                                $data['denyut_nadi']
                                ?? null,

                            'golongan_darah' =>
                                $data['golongan_darah']
                                ?? null,

                            'rhesus' =>
                                $data['rhesus']
                                ?? null,

                            'status_kelayakan' =>
                                $statusKelayakan,

                            'alasan_tidak_layak' =>
                                $alasanTidakLayak !== ''
                                    ? $alasanTidakLayak
                                    : null,

                            'catatan_medis' =>
                                $data['catatan_medis']
                                ?? null,

                            'diperiksa_pada' =>
                                $data['diperiksa_pada']
                                ?? now(),
                        ]
                    );

            $pendaftaranTerkunci->update([
                'status' =>
                    $statusKelayakan ===
                    StatusKelayakanDonor::Layak
                        ? StatusPendaftaranDonor::Layak
                        : StatusPendaftaranDonor
                            ::TidakLayak,
            ]);

            $this->perbaruiProfilPendonor(
                pendaftaran: $pendaftaranTerkunci,
                golonganDarah:
                    $data['golongan_darah'] ?? null,
                rhesus:
                    $data['rhesus'] ?? null,
            );

            return $pemeriksaan->refresh();
        });
    }

    private function pastikanPendaftaranDapatDiperiksa(
        PendaftaranDonor $pendaftaran
    ): void {
        $statusDiperbolehkan = [
            StatusPendaftaranDonor::Hadir,
            StatusPendaftaranDonor::Layak,
            StatusPendaftaranDonor::TidakLayak,
        ];

        if (
            ! in_array(
                $pendaftaran->status,
                $statusDiperbolehkan,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Pemeriksaan hanya dapat dilakukan untuk Pendonor yang sudah tercatat hadir.',
            ]);
        }
    }

    private function normalisasiStatusKelayakan(
        StatusKelayakanDonor|string $status
    ): StatusKelayakanDonor {
        return $status instanceof
            StatusKelayakanDonor
                ? $status
                : StatusKelayakanDonor::from($status);
    }

    private function perbaruiProfilPendonor(
        PendaftaranDonor $pendaftaran,
        mixed $golonganDarah,
        mixed $rhesus
    ): void {
        $profilPendonor =
            $pendaftaran
                ->pendonor
                ?->profilPendonor;

        if ($profilPendonor === null) {
            return;
        }

        $dataPerubahan = [];

        if (filled($golonganDarah)) {
            $dataPerubahan['golongan_darah'] =
                $golonganDarah;
        }

        if (filled($rhesus)) {
            $dataPerubahan['rhesus'] = $rhesus;
        }

        if ($dataPerubahan !== []) {
            $profilPendonor->update(
                $dataPerubahan
            );
        }
    }
}