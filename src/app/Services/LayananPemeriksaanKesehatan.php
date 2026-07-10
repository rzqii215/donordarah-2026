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
                        'kantongDarah',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($pendaftaran->id);

            $this->pastikanPendaftaranDapatDiperiksa(
                $pendaftaranTerkunci
            );

            $statusKelayakan =
                $this->normalisasiStatusKelayakan(
                    $data['status_kelayakan'] ?? null
                );

            $alasanTidakLayak =
                $this->normalisasiTeks(
                    $data['alasan_tidak_layak'] ?? null
                );

            if (
                $statusKelayakan === StatusKelayakanDonor::TidakLayak
                && $alasanTidakLayak === null
            ) {
                throw ValidationException::withMessages([
                    'alasan_tidak_layak' =>
                        'Alasan tidak layak wajib diisi.',
                ]);
            }

            if ($statusKelayakan === StatusKelayakanDonor::Layak) {
                $alasanTidakLayak = null;
            }

            $pemeriksaan =
                PemeriksaanKesehatan::query()
                    ->updateOrCreate(
                        [
                            'pendaftaran_donor_id' =>
                                $pendaftaranTerkunci->id,
                        ],
                        [
                            'diperiksa_oleh' => $petugasId,

                            'berat_badan_kg' =>
                                $data['berat_badan_kg'],

                            'tekanan_sistolik' =>
                                $data['tekanan_sistolik'],

                            'tekanan_diastolik' =>
                                $data['tekanan_diastolik'],

                            'kadar_hemoglobin' =>
                                $this->nilaiKosongMenjadiNull(
                                    $data['kadar_hemoglobin'] ?? null
                                ),

                            'suhu_tubuh' =>
                                $this->nilaiKosongMenjadiNull(
                                    $data['suhu_tubuh'] ?? null
                                ),

                            'denyut_nadi' =>
                                $this->nilaiKosongMenjadiNull(
                                    $data['denyut_nadi'] ?? null
                                ),

                            'golongan_darah' =>
                                $this->nilaiKosongMenjadiNull(
                                    $data['golongan_darah'] ?? null
                                ),

                            'rhesus' =>
                                $this->nilaiKosongMenjadiNull(
                                    $data['rhesus'] ?? null
                                ),

                            'status_kelayakan' =>
                                $statusKelayakan,

                            'alasan_tidak_layak' =>
                                $alasanTidakLayak,

                            'catatan_medis' =>
                                $this->normalisasiTeks(
                                    $data['catatan_medis'] ?? null
                                ),

                            'diperiksa_pada' =>
                                $data['diperiksa_pada'] ?? now(),
                        ]
                    );

            $pendaftaranTerkunci->update([
                'status' =>
                    $this->statusPendaftaranDariKelayakan(
                        $statusKelayakan
                    ),
            ]);

            $this->perbaruiProfilPendonor(
                pendaftaran: $pendaftaranTerkunci,
                golonganDarah:
                    $data['golongan_darah'] ?? null,
                rhesus:
                    $data['rhesus'] ?? null,
            );

            return $pemeriksaan
                ->refresh()
                ->load([
                    'pendaftaran.pendonor.profilPendonor',
                    'pendaftaran.jadwal',
                    'pemeriksa',
                ]);
        });
    }

    private function pastikanPendaftaranDapatDiperiksa(
        PendaftaranDonor $pendaftaran
    ): void {
        if (
            $pendaftaran->status !== StatusPendaftaranDonor::Hadir
        ) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Pemeriksaan kesehatan hanya dapat dibuat untuk pendaftaran donor berstatus Hadir.',
            ]);
        }

        if ($pendaftaran->kantongDarah !== null) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Pendaftaran donor yang sudah memiliki kantong darah tidak dapat diperiksa ulang.',
            ]);
        }
    }

    private function normalisasiStatusKelayakan(
        StatusKelayakanDonor|string|null $status
    ): StatusKelayakanDonor {
        if ($status instanceof StatusKelayakanDonor) {
            return $status;
        }

        if (blank($status)) {
            throw ValidationException::withMessages([
                'status_kelayakan' =>
                    'Status kelayakan wajib dipilih.',
            ]);
        }

        $statusKelayakan =
            StatusKelayakanDonor::tryFrom(
                (string) $status
            );

        if ($statusKelayakan === null) {
            throw ValidationException::withMessages([
                'status_kelayakan' =>
                    'Status kelayakan tidak valid.',
            ]);
        }

        return $statusKelayakan;
    }

    private function statusPendaftaranDariKelayakan(
        StatusKelayakanDonor $statusKelayakan
    ): StatusPendaftaranDonor {
        return match ($statusKelayakan) {
            StatusKelayakanDonor::Layak =>
                StatusPendaftaranDonor::Layak,

            StatusKelayakanDonor::TidakLayak =>
                StatusPendaftaranDonor::TidakLayak,
        };
    }

    private function normalisasiTeks(
        mixed $value
    ): ?string {
        if (blank($value)) {
            return null;
        }

        return trim((string) $value);
    }

    private function nilaiKosongMenjadiNull(
        mixed $value
    ): mixed {
        return blank($value) ? null : $value;
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

        if ($dataPerubahan === []) {
            return;
        }

        $profilPendonor->update($dataPerubahan);
    }
}