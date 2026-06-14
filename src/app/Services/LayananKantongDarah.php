<?php

namespace App\Services;

use App\Enums\GolonganDarah;
use App\Enums\JenisKomponenDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusKelayakanDonor;
use App\Enums\StatusMutuKantongDarah;
use App\Enums\StatusPendaftaranDonor;
use App\Models\KantongDarah;
use App\Models\PendaftaranDonor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LayananKantongDarah
{
    /**
     * @param array<string, mixed> $data
     */
    public function buat(
        PendaftaranDonor $pendaftaran,
        array $data
    ): KantongDarah {
        return DB::transaction(function () use (
            $pendaftaran,
            $data
        ): KantongDarah {
            $pendaftaranTerkunci =
                PendaftaranDonor::query()
                    ->with([
                        'pendonor.profilPendonor',
                        'pemeriksaanKesehatan',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($pendaftaran->id);

            $this->pastikanPendaftaranLayak(
                $pendaftaranTerkunci
            );

            $this->pastikanBelumMemilikiKantong(
                $pendaftaranTerkunci
            );

            $diambilPada = CarbonImmutable::parse(
                $data['diambil_pada'] ?? now()
            );

            $kedaluwarsaPada = CarbonImmutable::parse(
                $data['kedaluwarsa_pada']
            );

            if (
                $kedaluwarsaPada
                    ->lessThanOrEqualTo($diambilPada)
            ) {
                throw ValidationException::withMessages([
                    'kedaluwarsa_pada' =>
                        'Waktu kedaluwarsa harus setelah waktu pengambilan.',
                ]);
            }

            $golonganDarah =
                $data['golongan_darah']
                ?? $pendaftaranTerkunci
                    ->pemeriksaanKesehatan
                    ?->golongan_darah;

            $rhesus =
                $data['rhesus']
                ?? $pendaftaranTerkunci
                    ->pemeriksaanKesehatan
                    ?->rhesus;

            if (blank($golonganDarah)) {
                throw ValidationException::withMessages([
                    'golongan_darah' =>
                        'Golongan darah wajib tersedia.',
                ]);
            }

            if (blank($rhesus)) {
                throw ValidationException::withMessages([
                    'rhesus' =>
                        'Rhesus darah wajib tersedia.',
                ]);
            }

            $volume = (int) $data['volume_ml'];

            if ($volume < 1 || $volume > 1000) {
                throw ValidationException::withMessages([
                    'volume_ml' =>
                        'Volume kantong harus antara 1 sampai 1000 ml.',
                ]);
            }

            $kantong = KantongDarah::query()->create([
                'kode_kantong' =>
                    $this->buatKodeKantong(),

                'pendaftaran_donor_id' =>
                    $pendaftaranTerkunci->id,

                'golongan_darah' =>
                    $this->normalisasiGolonganDarah(
                        $golonganDarah
                    ),

                'rhesus' =>
                    $this->normalisasiRhesus($rhesus),

                'jenis_komponen' =>
                    $data['jenis_komponen']
                    ?? JenisKomponenDarah::DarahUtuh,

                'volume_ml' =>
                    $volume,

                'diambil_pada' =>
                    $diambilPada,

                'kedaluwarsa_pada' =>
                    $kedaluwarsaPada,

                'status_mutu' =>
                    StatusMutuKantongDarah::Menunggu,

                'status' =>
                    StatusKantongDarah::Menunggu,

                'lokasi_penyimpanan' =>
                    $data['lokasi_penyimpanan']
                    ?? null,

                'diverifikasi_oleh' =>
                    null,

                'diverifikasi_pada' =>
                    null,

                'alasan_penolakan' =>
                    null,

                'didistribusikan_pada' =>
                    null,

                'catatan' =>
                    $data['catatan'] ?? null,
            ]);

            $pendaftaranTerkunci->update([
                'status' =>
                    StatusPendaftaranDonor::Selesai,

                'selesai_pada' =>
                    $diambilPada,
            ]);

            $pendaftaranTerkunci
                ->pendonor
                ?->profilPendonor
                ?->update([
                    'terakhir_donor_pada' =>
                        $diambilPada,
                ]);

            return $kantong->refresh();
        });
    }

    public function luluskanMutu(
        KantongDarah $kantong,
        int $petugasId
    ): KantongDarah {
        return DB::transaction(function () use (
            $kantong,
            $petugasId
        ): KantongDarah {
            $record = KantongDarah::query()
                ->lockForUpdate()
                ->findOrFail($kantong->id);

            if (! $record->dapatDiverifikasi()) {
                throw ValidationException::withMessages([
                    'status_mutu' =>
                        'Kantong darah ini tidak dapat diverifikasi dari status saat ini.',
                ]);
            }

            if ($record->sudahKedaluwarsa()) {
                $record->update([
                    'status' =>
                        StatusKantongDarah::Kedaluwarsa,
                ]);

                throw ValidationException::withMessages([
                    'kedaluwarsa_pada' =>
                        'Kantong darah sudah melewati waktu kedaluwarsa.',
                ]);
            }

            $record->update([
                'status_mutu' =>
                    StatusMutuKantongDarah::Lulus,

                'status' =>
                    StatusKantongDarah::Tersedia,

                'diverifikasi_oleh' =>
                    $petugasId,

                'diverifikasi_pada' =>
                    now(),

                'alasan_penolakan' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    public function gagalkanMutu(
        KantongDarah $kantong,
        int $petugasId,
        string $alasan
    ): KantongDarah {
        return DB::transaction(function () use (
            $kantong,
            $petugasId,
            $alasan
        ): KantongDarah {
            $record = KantongDarah::query()
                ->lockForUpdate()
                ->findOrFail($kantong->id);

            if (! $record->dapatDiverifikasi()) {
                throw ValidationException::withMessages([
                    'status_mutu' =>
                        'Kantong darah ini tidak dapat ditolak dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan_penolakan' =>
                        'Alasan penolakan wajib diisi.',
                ]);
            }

            $record->update([
                'status_mutu' =>
                    StatusMutuKantongDarah::Gagal,

                'status' =>
                    StatusKantongDarah::Ditolak,

                'diverifikasi_oleh' =>
                    $petugasId,

                'diverifikasi_pada' =>
                    now(),

                'alasan_penolakan' =>
                    $alasanBersih,
            ]);

            return $record->refresh();
        });
    }

    public function tandaiRusak(
        KantongDarah $kantong,
        string $alasan
    ): KantongDarah {
        return DB::transaction(function () use (
            $kantong,
            $alasan
        ): KantongDarah {
            $record = KantongDarah::query()
                ->lockForUpdate()
                ->findOrFail($kantong->id);

            if (! $record->dapatDitandaiRusak()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Hanya kantong berstatus tersedia yang dapat ditandai rusak.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' =>
                        'Alasan kerusakan wajib diisi.',
                ]);
            }

            $catatanLama = trim(
                (string) $record->catatan
            );

            $catatanBaru = sprintf(
                'Kerusakan: %s',
                $alasanBersih
            );

            $record->update([
                'status' =>
                    StatusKantongDarah::Rusak,

                'catatan' =>
                    $catatanLama !== ''
                        ? $catatanLama
                            . PHP_EOL
                            . $catatanBaru
                        : $catatanBaru,
            ]);

            return $record->refresh();
        });
    }

    public function tandaiKedaluwarsa(
        KantongDarah $kantong
    ): KantongDarah {
        return DB::transaction(function () use (
            $kantong
        ): KantongDarah {
            $record = KantongDarah::query()
                ->lockForUpdate()
                ->findOrFail($kantong->id);

            if (
                $record->status !==
                    StatusKantongDarah::Tersedia
                || ! $record->sudahKedaluwarsa()
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Kantong belum dapat ditandai kedaluwarsa.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusKantongDarah::Kedaluwarsa,
            ]);

            return $record->refresh();
        });
    }

    private function pastikanPendaftaranLayak(
        PendaftaranDonor $pendaftaran
    ): void {
        if (
            $pendaftaran->status !==
            StatusPendaftaranDonor::Layak
        ) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Kantong darah hanya dapat dibuat dari pendaftaran berstatus Layak Donor.',
            ]);
        }

        $pemeriksaan =
            $pendaftaran->pemeriksaanKesehatan;

        if (
            $pemeriksaan === null
            || $pemeriksaan->status_kelayakan !==
                StatusKelayakanDonor::Layak
        ) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Pendaftaran belum mempunyai hasil pemeriksaan Layak Donor.',
            ]);
        }
    }

    private function pastikanBelumMemilikiKantong(
        PendaftaranDonor $pendaftaran
    ): void {
        $sudahAda = KantongDarah::withTrashed()
            ->where(
                'pendaftaran_donor_id',
                $pendaftaran->id
            )
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'pendaftaran_donor_id' =>
                    'Pendaftaran ini sudah memiliki kantong darah.',
            ]);
        }
    }

    private function buatKodeKantong(): string
    {
        do {
            $kode = sprintf(
                'BAG-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6))
            );
        } while (
            KantongDarah::withTrashed()
                ->where('kode_kantong', $kode)
                ->exists()
        );

        return $kode;
    }

    private function normalisasiGolonganDarah(
        GolonganDarah|string $golonganDarah
    ): GolonganDarah {
        return $golonganDarah instanceof GolonganDarah
            ? $golonganDarah
            : GolonganDarah::from($golonganDarah);
    }

    private function normalisasiRhesus(
        RhesusDarah|string $rhesus
    ): RhesusDarah {
        return $rhesus instanceof RhesusDarah
            ? $rhesus
            : RhesusDarah::from($rhesus);
    }
}