<?php

namespace App\Services;

use App\Enums\StatusItemPermintaanDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Enums\StatusPermintaanDarah;
use App\Models\ItemPermintaanDarah;
use App\Models\KantongDarah;
use App\Models\PermintaanDarah;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayananAlokasiDarah
{
    /**
     * Mengalokasikan kantong darah secara otomatis menggunakan
     * metode FEFO: kantong dengan kedaluwarsa terdekat dipilih dahulu.
     *
     * @return Collection<int, ItemPermintaanDarah>
     */
    public function alokasikanOtomatis(
        PermintaanDarah $permintaan,
        int $petugasId
    ): Collection {
        return DB::transaction(function () use (
            $permintaan,
            $petugasId
        ): Collection {
            $record = PermintaanDarah::query()
                ->with('itemAktif')
                ->lockForUpdate()
                ->findOrFail($permintaan->id);

            $this->pastikanPermintaanDapatDialokasikan(
                $record
            );

            $sisaKebutuhan =
                $record->sisaKebutuhanKantong();

            if ($sisaKebutuhan === 0) {
                $this->sinkronkanStatusPermintaan(
                    $record
                );

                return new Collection();
            }

            $kantongTersedia = KantongDarah::query()
                ->where(
                    'status',
                    StatusKantongDarah::Tersedia->value
                )
                ->where(
                    'status_mutu',
                    StatusMutuKantongDarah::Lulus->value
                )
                ->where(
                    'golongan_darah',
                    $record->golongan_darah->value
                )
                ->where(
                    'rhesus',
                    $record->rhesus->value
                )
                ->where(
                    'kedaluwarsa_pada',
                    '>',
                    now()
                )
                ->whereDoesntHave(
                    'alokasiAktif'
                )
                ->orderBy(
                    'kedaluwarsa_pada'
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->limit($sisaKebutuhan)
                ->get();

            $hasil = new Collection();

            foreach ($kantongTersedia as $kantong) {
                $item = $this->buatItemAlokasi(
                    permintaan: $record,
                    kantong: $kantong,
                    petugasId: $petugasId,
                    catatan: 'Dialokasikan otomatis menggunakan metode FEFO.',
                );

                $hasil->push($item);
            }

            $record->unsetRelation('itemAktif');

            $record->load(
                'itemAktif'
            );

            $this->sinkronkanStatusPermintaan(
                $record
            );

            return $hasil;
        });
    }

    public function alokasikanManual(
        PermintaanDarah $permintaan,
        KantongDarah $kantong,
        int $petugasId,
        ?string $catatan = null
    ): ItemPermintaanDarah {
        return DB::transaction(function () use (
            $permintaan,
            $kantong,
            $petugasId,
            $catatan
        ): ItemPermintaanDarah {
            $recordPermintaan =
                PermintaanDarah::query()
                    ->with('itemAktif')
                    ->lockForUpdate()
                    ->findOrFail($permintaan->id);

            $recordKantong =
                KantongDarah::query()
                    ->with('alokasiAktif')
                    ->lockForUpdate()
                    ->findOrFail($kantong->id);

            $this->pastikanPermintaanDapatDialokasikan(
                $recordPermintaan
            );

            if (
                $recordPermintaan
                    ->sisaKebutuhanKantong() === 0
            ) {
                throw ValidationException::withMessages([
                    'permintaan_darah_id' =>
                        'Seluruh kebutuhan kantong pada pengajuan ini sudah terpenuhi.',
                ]);
            }

            $this->pastikanKantongDapatDialokasikan(
                permintaan: $recordPermintaan,
                kantong: $recordKantong,
            );

            $item = $this->buatItemAlokasi(
                permintaan: $recordPermintaan,
                kantong: $recordKantong,
                petugasId: $petugasId,
                catatan: $catatan,
            );

            $recordPermintaan->unsetRelation(
                'itemAktif'
            );

            $recordPermintaan->load(
                'itemAktif'
            );

            $this->sinkronkanStatusPermintaan(
                $recordPermintaan
            );

            return $item->refresh();
        });
    }

    public function lepaskan(
        ItemPermintaanDarah $item,
        int $petugasId,
        string $alasan
    ): ItemPermintaanDarah {
        return DB::transaction(function () use (
            $item,
            $petugasId,
            $alasan
        ): ItemPermintaanDarah {
            $record = ItemPermintaanDarah::query()
                ->with([
                    'permintaan',
                    'kantongDarah',
                ])
                ->lockForUpdate()
                ->findOrFail($item->id);

            if (! $record->dapatDilepaskan()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Alokasi ini tidak dapat dilepaskan dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' =>
                        'Alasan pelepasan wajib diisi.',
                ]);
            }

            $kantong = KantongDarah::query()
                ->lockForUpdate()
                ->findOrFail(
                    $record->kantong_darah_id
                );

            $permintaan = PermintaanDarah::query()
                ->lockForUpdate()
                ->findOrFail(
                    $record->permintaan_darah_id
                );

            $record->update([
                'status' =>
                    StatusItemPermintaanDarah::Dilepaskan,

                'aktif' =>
                    null,

                'dilepas_oleh' =>
                    $petugasId,

                'dilepas_pada' =>
                    now(),

                'alasan_pelepasan' =>
                    $alasanBersih,
            ]);

            $kantong->update([
                'status' =>
                    $kantong->sudahKedaluwarsa()
                        ? StatusKantongDarah::Kedaluwarsa
                        : StatusKantongDarah::Tersedia,
            ]);

            $permintaan->update([
                'status' =>
                    StatusPermintaanDarah::MenungguStok,

                'siap_diambil_pada' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    private function buatItemAlokasi(
        PermintaanDarah $permintaan,
        KantongDarah $kantong,
        int $petugasId,
        ?string $catatan
    ): ItemPermintaanDarah {
        $item = ItemPermintaanDarah::query()->create([
            'permintaan_darah_id' =>
                $permintaan->id,

            'kantong_darah_id' =>
                $kantong->id,

            'status' =>
                StatusItemPermintaanDarah::Dialokasikan,

            'aktif' =>
                true,

            'dialokasikan_oleh' =>
                $petugasId,

            'dialokasikan_pada' =>
                now(),

            'dilepas_oleh' =>
                null,

            'dilepas_pada' =>
                null,

            'alasan_pelepasan' =>
                null,

            'didistribusikan_pada' =>
                null,

            'catatan' =>
                filled($catatan)
                    ? trim((string) $catatan)
                    : null,
        ]);

        $kantong->update([
            'status' =>
                StatusKantongDarah::Dipesan,
        ]);

        return $item;
    }

    private function pastikanPermintaanDapatDialokasikan(
        PermintaanDarah $permintaan
    ): void {
        if (! $permintaan->dapatDialokasikan()) {
            throw ValidationException::withMessages([
                'permintaan_darah_id' =>
                    'Pengajuan kebutuhan donor belum disetujui atau tidak dapat dialokasikan.',
            ]);
        }

        if ($permintaan->jumlah_kantong < 1) {
            throw ValidationException::withMessages([
                'jumlah_kantong' =>
                    'Jumlah kebutuhan kantong tidak valid.',
            ]);
        }
    }

    private function pastikanKantongDapatDialokasikan(
        PermintaanDarah $permintaan,
        KantongDarah $kantong
    ): void {
        if (! $kantong->dapatDialokasikan()) {
            throw ValidationException::withMessages([
                'kantong_darah_id' =>
                    'Kantong darah tidak tersedia, belum lulus mutu, atau sudah kedaluwarsa.',
            ]);
        }

        if ($kantong->alokasiAktif !== null) {
            throw ValidationException::withMessages([
                'kantong_darah_id' =>
                    'Kantong darah sudah dialokasikan pada pengajuan lain.',
            ]);
        }

        if (
            $kantong->golongan_darah !==
            $permintaan->golongan_darah
        ) {
            throw ValidationException::withMessages([
                'kantong_darah_id' =>
                    'Golongan darah kantong tidak sesuai dengan pengajuan kebutuhan donor.',
            ]);
        }

        if (
            $kantong->rhesus !==
            $permintaan->rhesus
        ) {
            throw ValidationException::withMessages([
                'kantong_darah_id' =>
                    'Rhesus kantong tidak sesuai dengan pengajuan kebutuhan donor.',
            ]);
        }
    }

    private function sinkronkanStatusPermintaan(
        PermintaanDarah $permintaan
    ): void {
        $jumlahDialokasikan =
            ItemPermintaanDarah::query()
                ->where(
                    'permintaan_darah_id',
                    $permintaan->id
                )
                ->where('aktif', true)
                ->count();

        if (
            $jumlahDialokasikan >=
            $permintaan->jumlah_kantong
        ) {
            $permintaan->update([
                'status' =>
                    StatusPermintaanDarah::SiapDiambil,

                'siap_diambil_pada' =>
                    $permintaan->siap_diambil_pada
                    ?? now(),
            ]);

            return;
        }

        $permintaan->update([
            'status' =>
                StatusPermintaanDarah::MenungguStok,

            'siap_diambil_pada' =>
                null,
        ]);
    }
}