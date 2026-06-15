<?php

namespace App\Services;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Models\KantongDarah;
use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LayananStokDarah
{
    private const BATAS_MENDEKATI_KEDALUWARSA_HARI = 7;

    /**
     * Menghasilkan ringkasan stok darah yang aman untuk API publik.
     *
     * @return array{
     *     data: array<int, array<string, mixed>>,
     *     meta: array<string, mixed>
     * }
     */
    public function ringkasanPublik(): array
    {
        $sekarang = CarbonImmutable::now();

        $batasMendekatiKedaluwarsa = $sekarang->addDays(
            self::BATAS_MENDEKATI_KEDALUWARSA_HARI
        );

        $hasilQuery = KantongDarah::query()
            ->select([
                'golongan_darah',
                'rhesus',
            ])
            ->selectRaw(
                'COUNT(*) AS jumlah_kantong'
            )
            ->selectRaw(
                'COALESCE(SUM(volume_ml), 0) AS total_volume_ml'
            )
            ->selectRaw(
                'MIN(kedaluwarsa_pada) AS kedaluwarsa_terdekat'
            )
            ->selectRaw(
                '
                    SUM(
                        CASE
                            WHEN kedaluwarsa_pada <= ?
                            THEN 1
                            ELSE 0
                        END
                    ) AS jumlah_mendekati_kedaluwarsa
                ',
                [
                    $batasMendekatiKedaluwarsa
                        ->toDateTimeString(),
                ]
            )
            ->where(
                'status',
                StatusKantongDarah::Tersedia->value
            )
            ->where(
                'status_mutu',
                StatusMutuKantongDarah::Lulus->value
            )
            ->where(
                'kedaluwarsa_pada',
                '>',
                $sekarang
            )
            ->groupBy([
                'golongan_darah',
                'rhesus',
            ])
            ->get();

        $stokBerdasarkanGolongan = $hasilQuery->keyBy(
            fn (Model $record): string => $this->buatKunci(
                golonganDarah: $this->nilaiEnum(
                    $record->getAttribute('golongan_darah')
                ),
                rhesus: $this->nilaiEnum(
                    $record->getAttribute('rhesus')
                ),
            )
        );

        $data = $this->buatSemuaKombinasiStok(
            $stokBerdasarkanGolongan
        );

        return [
            'data' => $data,

            'meta' => [
                'total_kantong_tersedia' => collect($data)
                    ->sum('jumlah_kantong'),

                'total_volume_ml' => collect($data)
                    ->sum('total_volume_ml'),

                'total_mendekati_kedaluwarsa' => collect($data)
                    ->sum('jumlah_mendekati_kedaluwarsa'),

                'batas_mendekati_kedaluwarsa_hari' =>
                    self::BATAS_MENDEKATI_KEDALUWARSA_HARI,

                'kriteria_stok' => [
                    'status' =>
                        StatusKantongDarah::Tersedia->value,

                    'status_mutu' =>
                        StatusMutuKantongDarah::Lulus->value,

                    'belum_kedaluwarsa' => true,
                ],

                'diperbarui_pada' =>
                    $sekarang->toIso8601String(),
            ],
        ];
    }

    /**
     * @param Collection<string, Model> $stokBerdasarkanGolongan
     *
     * @return array<int, array<string, mixed>>
     */
    private function buatSemuaKombinasiStok(
        Collection $stokBerdasarkanGolongan
    ): array {
        $data = [];

        foreach (GolonganDarah::cases() as $golonganDarah) {
            foreach (RhesusDarah::cases() as $rhesus) {
                $kunci = $this->buatKunci(
                    golonganDarah: $golonganDarah->value,
                    rhesus: $rhesus->value,
                );

                $record = $stokBerdasarkanGolongan->get(
                    $kunci
                );

                $data[] = [
                    'kode' => sprintf(
                        '%s%s',
                        $golonganDarah->label(),
                        $rhesus->simbol(),
                    ),

                    'golongan_darah' => [
                        'value' => $golonganDarah->value,
                        'label' => $golonganDarah->label(),
                    ],

                    'rhesus' => [
                        'value' => $rhesus->value,
                        'label' => $rhesus->label(),
                        'simbol' => $rhesus->simbol(),
                    ],

                    'jumlah_kantong' => $record !== null
                        ? (int) $record->getAttribute(
                            'jumlah_kantong'
                        )
                        : 0,

                    'total_volume_ml' => $record !== null
                        ? (int) $record->getAttribute(
                            'total_volume_ml'
                        )
                        : 0,

                    'jumlah_mendekati_kedaluwarsa' =>
                        $record !== null
                            ? (int) $record->getAttribute(
                                'jumlah_mendekati_kedaluwarsa'
                            )
                            : 0,

                    'kedaluwarsa_terdekat' =>
                        $record !== null
                            ? $this->formatTanggal(
                                $record->getAttribute(
                                    'kedaluwarsa_terdekat'
                                )
                            )
                            : null,
                ];
            }
        }

        return $data;
    }

    private function buatKunci(
        string $golonganDarah,
        string $rhesus
    ): string {
        return $golonganDarah . '|' . $rhesus;
    }

    private function nilaiEnum(
        mixed $nilai
    ): string {
        if ($nilai instanceof BackedEnum) {
            return (string) $nilai->value;
        }

        return (string) $nilai;
    }

    private function formatTanggal(
        mixed $tanggal
    ): ?string {
        if ($tanggal instanceof CarbonInterface) {
            return $tanggal->toIso8601String();
        }

        if (blank($tanggal)) {
            return null;
        }

        return CarbonImmutable::parse(
            (string) $tanggal
        )->toIso8601String();
    }
}