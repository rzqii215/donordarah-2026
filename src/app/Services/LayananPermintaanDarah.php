<?php

namespace App\Services;

use App\Enums\StatusPengguna;
use App\Enums\StatusPermintaanDarah;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LayananPermintaanDarah
{
    /**
     * @param array<string, mixed> $data
     */
    public function buat(
        ProfilRumahSakit $rumahSakit,
        array $data
    ): PermintaanDarah {
        return DB::transaction(function () use (
            $rumahSakit,
            $data
        ): PermintaanDarah {
            $rumahSakitTerkunci =
                ProfilRumahSakit::query()
                    ->with('pengguna')
                    ->lockForUpdate()
                    ->findOrFail($rumahSakit->id);

            $this->pastikanRumahSakitAktif(
                $rumahSakitTerkunci
            );

            $jumlahKantong =
                (int) $data['jumlah_kantong'];

            if ($jumlahKantong < 1) {
                throw ValidationException::withMessages([
                    'jumlah_kantong' =>
                        'Jumlah kantong minimal satu.',
                ]);
            }

            return PermintaanDarah::query()->create([
                'nomor_permintaan' =>
                    $this->buatNomorPermintaan(),

                'profil_rumah_sakit_id' =>
                    $rumahSakitTerkunci->id,

                'referensi_pasien' =>
                    $data['referensi_pasien'],

                'nama_dokter' =>
                    $data['nama_dokter'],

                'golongan_darah' =>
                    $data['golongan_darah'],

                'rhesus' =>
                    $data['rhesus'],

                'jumlah_kantong' =>
                    $jumlahKantong,

                'tingkat_urgensi' =>
                    $data['tingkat_urgensi'],

                'dibutuhkan_pada' =>
                    $data['dibutuhkan_pada'],

                'path_dokumen_permintaan' =>
                    $data[
                        'path_dokumen_permintaan'
                    ] ?? null,

                'status' =>
                    StatusPermintaanDarah::Diajukan,

                'ditinjau_oleh' =>
                    null,

                'ditinjau_pada' =>
                    null,

                'disetujui_pada' =>
                    null,

                'siap_diambil_pada' =>
                    null,

                'selesai_pada' =>
                    null,

                'dibatalkan_pada' =>
                    null,

                'alasan_penolakan' =>
                    null,

                'alasan_pembatalan' =>
                    null,

                'catatan' =>
                    $data['catatan'] ?? null,
            ]);
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function perbarui(
        PermintaanDarah $permintaan,
        array $data
    ): PermintaanDarah {
        return DB::transaction(function () use (
            $permintaan,
            $data
        ): PermintaanDarah {
            $record = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            if (! $record->dapatDiubah()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Permintaan tidak dapat diubah dari status saat ini.',
                ]);
            }

            $jumlahKantong =
                (int) $data['jumlah_kantong'];

            if ($jumlahKantong < 1) {
                throw ValidationException::withMessages([
                    'jumlah_kantong' =>
                        'Jumlah kantong minimal satu.',
                ]);
            }

            $record->update([
                'referensi_pasien' =>
                    $data['referensi_pasien'],

                'nama_dokter' =>
                    $data['nama_dokter'],

                'golongan_darah' =>
                    $data['golongan_darah'],

                'rhesus' =>
                    $data['rhesus'],

                'jumlah_kantong' =>
                    $jumlahKantong,

                'tingkat_urgensi' =>
                    $data['tingkat_urgensi'],

                'dibutuhkan_pada' =>
                    $data['dibutuhkan_pada'],

                'path_dokumen_permintaan' =>
                    $data[
                        'path_dokumen_permintaan'
                    ] ?? $record
                        ->path_dokumen_permintaan,

                'catatan' =>
                    $data['catatan'] ?? null,
            ]);

            return $record->refresh();
        });
    }

    public function tandaiDitinjau(
        PermintaanDarah $permintaan,
        int $petugasId
    ): PermintaanDarah {
        return DB::transaction(function () use (
            $permintaan,
            $petugasId
        ): PermintaanDarah {
            $record = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            if (! $record->dapatDitinjau()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Permintaan ini tidak dapat ditinjau dari status saat ini.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPermintaanDarah::Ditinjau,

                'ditinjau_oleh' =>
                    $petugasId,

                'ditinjau_pada' =>
                    now(),

                'alasan_penolakan' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    public function setujui(
        PermintaanDarah $permintaan,
        int $petugasId
    ): PermintaanDarah {
        return DB::transaction(function () use (
            $permintaan,
            $petugasId
        ): PermintaanDarah {
            $record = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            if (! $record->dapatDisetujui()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Permintaan ini tidak dapat disetujui dari status saat ini.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPermintaanDarah::Disetujui,

                'ditinjau_oleh' =>
                    $petugasId,

                'ditinjau_pada' =>
                    $record->ditinjau_pada ?? now(),

                'disetujui_pada' =>
                    now(),

                'alasan_penolakan' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    public function tandaiMenungguStok(
        PermintaanDarah $permintaan,
        int $petugasId
    ): PermintaanDarah {
        return DB::transaction(function () use (
            $permintaan,
            $petugasId
        ): PermintaanDarah {
            $record = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            if (
                ! in_array(
                    $record->status,
                    [
                        StatusPermintaanDarah::Diajukan,
                        StatusPermintaanDarah::Ditinjau,
                        StatusPermintaanDarah::Disetujui,
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Permintaan tidak dapat dipindahkan ke status menunggu stok.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPermintaanDarah::MenungguStok,

                'ditinjau_oleh' =>
                    $petugasId,

                'ditinjau_pada' =>
                    $record->ditinjau_pada ?? now(),
            ]);

            return $record->refresh();
        });
    }

    public function tolak(
        PermintaanDarah $permintaan,
        int $petugasId,
        string $alasan
    ): PermintaanDarah {
        return DB::transaction(function () use (
            $permintaan,
            $petugasId,
            $alasan
        ): PermintaanDarah {
            $record = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            if (! $record->dapatDitolak()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Permintaan ini tidak dapat ditolak dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' =>
                        'Alasan penolakan wajib diisi.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPermintaanDarah::Ditolak,

                'ditinjau_oleh' =>
                    $petugasId,

                'ditinjau_pada' =>
                    now(),

                'alasan_penolakan' =>
                    $alasanBersih,

                'disetujui_pada' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    public function batalkan(
        PermintaanDarah $permintaan,
        string $alasan
    ): PermintaanDarah {
        return DB::transaction(function () use (
            $permintaan,
            $alasan
        ): PermintaanDarah {
            $record = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            if (! $record->dapatDibatalkan()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Permintaan ini tidak dapat dibatalkan dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' =>
                        'Alasan pembatalan wajib diisi.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPermintaanDarah::Dibatalkan,

                'dibatalkan_pada' =>
                    now(),

                'alasan_pembatalan' =>
                    $alasanBersih,
            ]);

            return $record->refresh();
        });
    }

    private function pastikanRumahSakitAktif(
        ProfilRumahSakit $rumahSakit
    ): void {
        if (
            $rumahSakit->status_verifikasi !==
            StatusVerifikasiRumahSakit::Disetujui
        ) {
            throw ValidationException::withMessages([
                'profil_rumah_sakit_id' =>
                    'Rumah Sakit belum disetujui.',
            ]);
        }

        if (
            $rumahSakit->pengguna === null
            || $rumahSakit->pengguna->status !==
                StatusPengguna::Aktif
        ) {
            throw ValidationException::withMessages([
                'profil_rumah_sakit_id' =>
                    'Akun Rumah Sakit tidak aktif.',
            ]);
        }
    }

    private function buatNomorPermintaan(): string
    {
        do {
            $nomor = sprintf(
                'REQ-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6))
            );
        } while (
            PermintaanDarah::withTrashed()
                ->where(
                    'nomor_permintaan',
                    $nomor
                )
                ->exists()
        );

        return $nomor;
    }
}