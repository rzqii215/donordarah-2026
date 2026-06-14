<?php

namespace App\Services;

use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusItemPermintaanDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\ItemPermintaanDarah;
use App\Models\KantongDarah;
use App\Models\PermintaanDarah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LayananDistribusiDarah
{
    /**
     * @param array<string, mixed> $data
     */
    public function buat(
        PermintaanDarah $permintaan,
        int $petugasId,
        array $data
    ): DistribusiDarah {
        return DB::transaction(function () use (
            $permintaan,
            $petugasId,
            $data
        ): DistribusiDarah {
            $record = PermintaanDarah::query()
                ->with([
                    'distribusi',
                    'itemAktif.kantongDarah',
                ])
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            $this->pastikanPermintaanSiapDistribusi(
                $record
            );

            if ($record->distribusi !== null) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Permintaan ini sudah mempunyai data distribusi.',
                ]);
            }

            return DistribusiDarah::query()->create([
                'nomor_distribusi' =>
                    $this->buatNomorDistribusi(),

                'permintaan_darah_id' =>
                    $record->id,

                'disiapkan_oleh' =>
                    $petugasId,

                'dijadwalkan_pada' =>
                    $data['dijadwalkan_pada'],

                'status' =>
                    StatusDistribusiDarah::Dijadwalkan,

                'diserahkan_oleh' =>
                    null,

                'nama_penerima' =>
                    null,

                'jabatan_penerima' =>
                    null,

                'nomor_identitas_penerima' =>
                    null,

                'path_bukti_serah_terima' =>
                    null,

                'diserahkan_pada' =>
                    null,

                'dibatalkan_pada' =>
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
        DistribusiDarah $distribusi,
        array $data
    ): DistribusiDarah {
        return DB::transaction(function () use (
            $distribusi,
            $data
        ): DistribusiDarah {
            $record = DistribusiDarah::query()
                ->lockForUpdate()
                ->findOrFail($distribusi->id);

            if (! $record->dapatDiubah()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi tidak dapat diubah dari status saat ini.',
                ]);
            }

            $record->update([
                'dijadwalkan_pada' =>
                    $data['dijadwalkan_pada'],

                'catatan' =>
                    $data['catatan'] ?? null,
            ]);

            return $record->refresh();
        });
    }

    public function tandaiSiap(
        DistribusiDarah $distribusi
    ): DistribusiDarah {
        return DB::transaction(function () use (
            $distribusi
        ): DistribusiDarah {
            $record = DistribusiDarah::query()
                ->with([
                    'permintaan.itemAktif.kantongDarah',
                ])
                ->lockForUpdate()
                ->findOrFail($distribusi->id);

            if (! $record->dapatDitandaiSiap()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi tidak dapat ditandai siap dari status saat ini.',
                ]);
            }

            $this->pastikanPermintaanSiapDistribusi(
                $record->permintaan
            );

            $record->update([
                'status' =>
                    StatusDistribusiDarah::SiapDiserahkan,
            ]);

            return $record->refresh();
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function selesaikan(
        DistribusiDarah $distribusi,
        int $petugasId,
        array $data
    ): DistribusiDarah {
        return DB::transaction(function () use (
            $distribusi,
            $petugasId,
            $data
        ): DistribusiDarah {
            $record = DistribusiDarah::query()
                ->lockForUpdate()
                ->findOrFail($distribusi->id);

            if (! $record->dapatDiselesaikan()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi tidak dapat diselesaikan dari status saat ini.',
                ]);
            }

            $namaPenerima = trim(
                (string) ($data['nama_penerima'] ?? '')
            );

            $jabatanPenerima = trim(
                (string) (
                    $data['jabatan_penerima'] ?? ''
                )
            );

            if ($namaPenerima === '') {
                throw ValidationException::withMessages([
                    'nama_penerima' =>
                        'Nama penerima wajib diisi.',
                ]);
            }

            if ($jabatanPenerima === '') {
                throw ValidationException::withMessages([
                    'jabatan_penerima' =>
                        'Jabatan penerima wajib diisi.',
                ]);
            }

            $permintaan = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail(
                    $record->permintaan_darah_id
                );

            if (
                $permintaan->status !==
                StatusPermintaanDarah::SiapDiambil
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Permintaan tidak berada pada status Siap Diambil.',
                ]);
            }

            $itemAktif = ItemPermintaanDarah::query()
                ->where(
                    'permintaan_darah_id',
                    $permintaan->id
                )
                ->where('aktif', true)
                ->lockForUpdate()
                ->get();

            if (
                $itemAktif->count() <
                $permintaan->jumlah_kantong
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Jumlah kantong yang dialokasikan belum memenuhi permintaan.',
                ]);
            }

            foreach ($itemAktif as $item) {
                if (! $item->dapatDidistribusikan()) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'Terdapat item alokasi yang tidak dapat didistribusikan.',
                    ]);
                }

                $kantong = KantongDarah::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $item->kantong_darah_id
                    );

                if (
                    $kantong->status !==
                    StatusKantongDarah::Dipesan
                ) {
                    throw ValidationException::withMessages([
                        'kantong_darah_id' =>
                            'Terdapat kantong darah yang tidak berstatus Dialokasikan.',
                    ]);
                }

                $item->update([
                    'status' =>
                        StatusItemPermintaanDarah
                            ::Didistribusikan,

                    'aktif' =>
                        null,

                    'didistribusikan_pada' =>
                        now(),
                ]);

                $kantong->update([
                    'status' =>
                        StatusKantongDarah
                            ::Didistribusikan,

                    'didistribusikan_pada' =>
                        now(),
                ]);
            }

            $record->update([
                'status' =>
                    StatusDistribusiDarah::Selesai,

                'diserahkan_oleh' =>
                    $petugasId,

                'nama_penerima' =>
                    $namaPenerima,

                'jabatan_penerima' =>
                    $jabatanPenerima,

                'nomor_identitas_penerima' =>
                    filled(
                        $data[
                            'nomor_identitas_penerima'
                        ] ?? null
                    )
                        ? trim(
                            (string) $data[
                                'nomor_identitas_penerima'
                            ]
                        )
                        : null,

                'path_bukti_serah_terima' =>
                    $data[
                        'path_bukti_serah_terima'
                    ] ?? null,

                'diserahkan_pada' =>
                    now(),

                'dibatalkan_pada' =>
                    null,

                'alasan_pembatalan' =>
                    null,
            ]);

            $permintaan->update([
                'status' =>
                    StatusPermintaanDarah::Selesai,

                'selesai_pada' =>
                    now(),
            ]);

            return $record->refresh();
        });
    }

    public function batalkan(
        DistribusiDarah $distribusi,
        int $petugasId,
        string $alasan
    ): DistribusiDarah {
        return DB::transaction(function () use (
            $distribusi,
            $petugasId,
            $alasan
        ): DistribusiDarah {
            $record = DistribusiDarah::query()
                ->lockForUpdate()
                ->findOrFail($distribusi->id);

            if (! $record->dapatDibatalkan()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi tidak dapat dibatalkan dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' =>
                        'Alasan pembatalan wajib diisi.',
                ]);
            }

            $permintaan = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail(
                    $record->permintaan_darah_id
                );

            $itemAktif = ItemPermintaanDarah::query()
                ->where(
                    'permintaan_darah_id',
                    $permintaan->id
                )
                ->where('aktif', true)
                ->lockForUpdate()
                ->get();

            foreach ($itemAktif as $item) {
                $kantong = KantongDarah::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $item->kantong_darah_id
                    );

                $item->update([
                    'status' =>
                        StatusItemPermintaanDarah
                            ::Dilepaskan,

                    'aktif' =>
                        null,

                    'dilepas_oleh' =>
                        $petugasId,

                    'dilepas_pada' =>
                        now(),

                    'alasan_pelepasan' =>
                        'Distribusi dibatalkan: '
                        . $alasanBersih,
                ]);

                $kantong->update([
                    'status' =>
                        $kantong->sudahKedaluwarsa()
                            ? StatusKantongDarah
                                ::Kedaluwarsa
                            : StatusKantongDarah
                                ::Tersedia,
                ]);
            }

            $record->update([
                'status' =>
                    StatusDistribusiDarah::Dibatalkan,

                'dibatalkan_pada' =>
                    now(),

                'alasan_pembatalan' =>
                    $alasanBersih,
            ]);

            $permintaan->update([
                'status' =>
                    StatusPermintaanDarah
                        ::MenungguStok,

                'siap_diambil_pada' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    private function pastikanPermintaanSiapDistribusi(
        PermintaanDarah $permintaan
    ): void {
        if (
            $permintaan->status !==
            StatusPermintaanDarah::SiapDiambil
        ) {
            throw ValidationException::withMessages([
                'permintaan_darah_id' =>
                    'Distribusi hanya dapat dibuat untuk permintaan berstatus Siap Diambil.',
            ]);
        }

        $jumlahItemAktif = $permintaan
            ->relationLoaded('itemAktif')
                ? $permintaan->itemAktif->count()
                : $permintaan
                    ->itemAktif()
                    ->count();

        if (
            $jumlahItemAktif <
            $permintaan->jumlah_kantong
        ) {
            throw ValidationException::withMessages([
                'permintaan_darah_id' =>
                    'Jumlah kantong darah yang dialokasikan belum memenuhi permintaan.',
            ]);
        }

        $itemAktif = $permintaan
            ->relationLoaded('itemAktif')
                ? $permintaan->itemAktif
                : $permintaan
                    ->itemAktif()
                    ->with('kantongDarah')
                    ->get();

        foreach ($itemAktif as $item) {
            if (
                $item->status !==
                StatusItemPermintaanDarah
                    ::Dialokasikan
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Terdapat item yang tidak berstatus Dialokasikan.',
                ]);
            }

            if (
                $item->kantongDarah === null
                || $item->kantongDarah->status !==
                    StatusKantongDarah::Dipesan
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Terdapat kantong darah yang tidak siap didistribusikan.',
                ]);
            }
        }
    }

    private function buatNomorDistribusi(): string
    {
        do {
            $nomor = sprintf(
                'DST-%s-%s',
                now()->format('Ymd'),
                Str::upper(
                    Str::random(6)
                )
            );
        } while (
            DistribusiDarah::query()
                ->where(
                    'nomor_distribusi',
                    $nomor
                )
                ->exists()
        );

        return $nomor;
    }
}