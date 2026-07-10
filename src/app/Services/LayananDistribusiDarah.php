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
                    'itemAktif.kantongDarah',
                ])
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            $this->pastikanPermintaanSiapDistribusi($record);

            $sudahAdaDistribusi = DistribusiDarah::query()
                ->where('permintaan_darah_id', $record->id)
                ->exists();

            if ($sudahAdaDistribusi) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Pengajuan ini sudah mempunyai data distribusi.',
                ]);
            }

            return DistribusiDarah::query()->create([
                'nomor_distribusi' => $this->buatNomorDistribusi(),
                'permintaan_darah_id' => $record->id,
                'disiapkan_oleh' => $petugasId,
                'dijadwalkan_pada' => $data['dijadwalkan_pada'],
                'status' => StatusDistribusiDarah::Dijadwalkan->value,
                'diserahkan_oleh' => null,
                'nama_penerima' => null,
                'jabatan_penerima' => null,
                'nomor_identitas_penerima' => null,
                'path_bukti_serah_terima' => null,
                'diserahkan_pada' => null,
                'dibatalkan_pada' => null,
                'alasan_pembatalan' => null,
                'catatan' => $data['catatan'] ?? null,
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

            if (
                $this->statusDistribusiValue($record) !==
                StatusDistribusiDarah::Dijadwalkan->value
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi tidak dapat diubah dari status saat ini.',
                ]);
            }

            $record->update([
                'dijadwalkan_pada' => $data['dijadwalkan_pada'],
                'catatan' => $data['catatan'] ?? null,
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
                ->lockForUpdate()
                ->findOrFail($distribusi->id);

            if (
                $this->statusDistribusiValue($record) !==
                StatusDistribusiDarah::Dijadwalkan->value
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi hanya dapat ditandai siap dari status Dijadwalkan.',
                ]);
            }

            $permintaan = PermintaanDarah::query()
                ->with([
                    'itemAktif.kantongDarah',
                ])
                ->lockForUpdate()
                ->findOrFail($record->permintaan_darah_id);

            $this->pastikanPermintaanSiapDistribusi($permintaan);

            $record->update([
                'status' => StatusDistribusiDarah::SiapDiserahkan->value,
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

            if (
                ! in_array(
                    $this->statusDistribusiValue($record),
                    [
                        StatusDistribusiDarah::Dijadwalkan->value,
                        StatusDistribusiDarah::SiapDiserahkan->value,
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi tidak dapat diselesaikan dari status saat ini.',
                ]);
            }

            $namaPenerima = trim(
                (string) ($data['nama_penerima'] ?? '')
            );

            $jabatanPenerima = trim(
                (string) ($data['jabatan_penerima'] ?? '')
            );

            if ($namaPenerima === '') {
                throw ValidationException::withMessages([
                    'nama_penerima' => 'Nama penerima wajib diisi.',
                ]);
            }

            if ($jabatanPenerima === '') {
                throw ValidationException::withMessages([
                    'jabatan_penerima' => 'Jabatan penerima wajib diisi.',
                ]);
            }

            $permintaan = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($record->permintaan_darah_id);

            if (
                $this->statusPermintaanValue($permintaan) !==
                StatusPermintaanDarah::SiapDiambil->value
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Pengajuan tidak berada pada status Siap Diambil.',
                ]);
            }

            $itemAktif = ItemPermintaanDarah::query()
                ->where('permintaan_darah_id', $permintaan->id)
                ->where('aktif', true)
                ->lockForUpdate()
                ->get();

            if ($itemAktif->count() < (int) $permintaan->jumlah_kantong) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Jumlah kantong yang dialokasikan belum memenuhi pengajuan.',
                ]);
            }

            foreach ($itemAktif as $item) {
                if (
                    $this->statusItemValue($item) !==
                    StatusItemPermintaanDarah::Dialokasikan->value
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'Terdapat item alokasi yang tidak berstatus Dialokasikan.',
                    ]);
                }

                $kantong = KantongDarah::query()
                    ->lockForUpdate()
                    ->findOrFail($item->kantong_darah_id);

                if (
                    $this->statusKantongValue($kantong) !==
                    StatusKantongDarah::Dipesan->value
                ) {
                    throw ValidationException::withMessages([
                        'kantong_darah_id' =>
                            'Terdapat kantong darah yang belum berstatus Dialokasikan.',
                    ]);
                }

                $item->update([
                    'status' =>
                        StatusItemPermintaanDarah::Didistribusikan->value,
                    'aktif' => false,
                    'didistribusikan_pada' => now(),
                ]);

                $kantong->update([
                    'status' => StatusKantongDarah::Didistribusikan->value,
                    'didistribusikan_pada' => now(),
                ]);
            }

            $record->update([
                'status' => StatusDistribusiDarah::Selesai->value,
                'diserahkan_oleh' => $petugasId,
                'nama_penerima' => $namaPenerima,
                'jabatan_penerima' => $jabatanPenerima,
                'nomor_identitas_penerima' => filled(
                    $data['nomor_identitas_penerima'] ?? null
                )
                    ? trim((string) $data['nomor_identitas_penerima'])
                    : null,
                'path_bukti_serah_terima' =>
                    $data['path_bukti_serah_terima'] ?? null,
                'diserahkan_pada' => now(),
                'dibatalkan_pada' => null,
                'alasan_pembatalan' => null,
            ]);

            $permintaan->update([
                'status' => StatusPermintaanDarah::Selesai->value,
                'selesai_pada' => now(),
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

            if (
                ! in_array(
                    $this->statusDistribusiValue($record),
                    [
                        StatusDistribusiDarah::Dijadwalkan->value,
                        StatusDistribusiDarah::SiapDiserahkan->value,
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Distribusi tidak dapat dibatalkan dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' => 'Alasan pembatalan wajib diisi.',
                ]);
            }

            $permintaan = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail($record->permintaan_darah_id);

            $itemAktif = ItemPermintaanDarah::query()
                ->where('permintaan_darah_id', $permintaan->id)
                ->where('aktif', true)
                ->lockForUpdate()
                ->get();

            foreach ($itemAktif as $item) {
                $kantong = KantongDarah::query()
                    ->lockForUpdate()
                    ->findOrFail($item->kantong_darah_id);

                $item->update([
                    'status' => StatusItemPermintaanDarah::Dilepaskan->value,
                    'aktif' => false,
                    'dilepas_oleh' => $petugasId,
                    'dilepas_pada' => now(),
                    'alasan_pelepasan' =>
                        'Distribusi dibatalkan: ' . $alasanBersih,
                ]);

                $kantong->update([
                    'status' => $kantong->sudahKedaluwarsa()
                        ? StatusKantongDarah::Kedaluwarsa->value
                        : StatusKantongDarah::Tersedia->value,
                    'didistribusikan_pada' => null,
                ]);
            }

            $record->update([
                'status' => StatusDistribusiDarah::Dibatalkan->value,
                'dibatalkan_pada' => now(),
                'alasan_pembatalan' => $alasanBersih,
            ]);

            $permintaan->update([
                'status' => StatusPermintaanDarah::MenungguStok->value,
                'siap_diambil_pada' => null,
            ]);

            return $record->refresh();
        });
    }

    private function pastikanPermintaanSiapDistribusi(
        PermintaanDarah $permintaan
    ): void {
        if (
            $this->statusPermintaanValue($permintaan) !==
            StatusPermintaanDarah::SiapDiambil->value
        ) {
            throw ValidationException::withMessages([
                'permintaan_darah_id' =>
                    'Distribusi hanya dapat dibuat untuk pengajuan berstatus Siap Diambil.',
            ]);
        }

        $itemAktif = $permintaan->relationLoaded('itemAktif')
            ? $permintaan->itemAktif
            : $permintaan
                ->itemAktif()
                ->with('kantongDarah')
                ->get();

        if ($itemAktif->count() < (int) $permintaan->jumlah_kantong) {
            throw ValidationException::withMessages([
                'permintaan_darah_id' =>
                    'Jumlah kantong darah yang dialokasikan belum memenuhi pengajuan.',
            ]);
        }

        foreach ($itemAktif as $item) {
            if (
                $this->statusItemValue($item) !==
                StatusItemPermintaanDarah::Dialokasikan->value
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Terdapat item alokasi yang tidak berstatus Dialokasikan.',
                ]);
            }

            if (
                $item->kantongDarah === null
                || $this->statusKantongValue($item->kantongDarah) !==
                    StatusKantongDarah::Dipesan->value
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Terdapat kantong darah yang belum siap didistribusikan.',
                ]);
            }
        }
    }

    private function buatNomorDistribusi(): string
    {
        $tanggal = now()->format('Ymd');

        $nomorUrut = DistribusiDarah::query()
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        do {
            $nomorDistribusi = 'DST-' . $tanggal . '-' . str_pad(
                (string) $nomorUrut,
                4,
                '0',
                STR_PAD_LEFT
            );

            $sudahAda = DistribusiDarah::query()
                ->where('nomor_distribusi', $nomorDistribusi)
                ->exists();

            $nomorUrut++;
        } while ($sudahAda);

        return $nomorDistribusi;
    }

    private function statusDistribusiValue(
        DistribusiDarah $distribusi
    ): string {
        return $distribusi->status instanceof StatusDistribusiDarah
            ? $distribusi->status->value
            : (string) $distribusi->status;
    }

    private function statusPermintaanValue(
        PermintaanDarah $permintaan
    ): string {
        return $permintaan->status instanceof StatusPermintaanDarah
            ? $permintaan->status->value
            : (string) $permintaan->status;
    }

    private function statusItemValue(
        ItemPermintaanDarah $item
    ): string {
        return $item->status instanceof StatusItemPermintaanDarah
            ? $item->status->value
            : (string) $item->status;
    }

    private function statusKantongValue(
        KantongDarah $kantong
    ): string {
        return $kantong->status instanceof StatusKantongDarah
            ? $kantong->status->value
            : (string) $kantong->status;
    }
}