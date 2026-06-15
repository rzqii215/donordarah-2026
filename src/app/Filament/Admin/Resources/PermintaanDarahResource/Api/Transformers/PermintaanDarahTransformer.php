<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api\Transformers;

use App\Models\PermintaanDarah;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property PermintaanDarah $resource
 */
class PermintaanDarahTransformer extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        $rumahSakit = $this->resource
            ->rumahSakit;

        $peninjau = $this->resource
            ->peninjau;

        $pathDokumen = $this->resource
            ->getAttribute(
                'path_dokumen_permintaan'
            );

        return [
            'id' => $this->resource->getKey(),

            'nomor_permintaan' =>
                $this->resource
                    ->nomor_permintaan,

            'rumah_sakit' => [
                'id' =>
                    $rumahSakit?->getKey(),

                'kode' =>
                    $rumahSakit
                        ?->kode_rumah_sakit,

                'nama' =>
                    $rumahSakit
                        ?->nama_rumah_sakit,
            ],

            'referensi_pasien' =>
                $this->resource
                    ->referensi_pasien,

            'nama_dokter' =>
                $this->resource
                    ->nama_dokter,

            'kebutuhan' => [
                'golongan_darah' => [
                    'value' =>
                        $this->nilaiEnum(
                            $this->resource
                                ->golongan_darah
                        ),

                    'label' =>
                        $this->labelEnum(
                            $this->resource
                                ->golongan_darah
                        ),
                ],

                'rhesus' => [
                    'value' =>
                        $this->nilaiEnum(
                            $this->resource->rhesus
                        ),

                    'label' =>
                        $this->labelEnum(
                            $this->resource->rhesus
                        ),

                    'simbol' =>
                        is_object(
                            $this->resource->rhesus
                        )
                        && method_exists(
                            $this->resource->rhesus,
                            'simbol'
                        )
                            ? $this->resource
                                ->rhesus
                                ->simbol()
                            : null,
                ],

                'jumlah_kantong' =>
                    (int) $this->resource
                        ->jumlah_kantong,

                'tingkat_urgensi' => [
                    'value' =>
                        $this->nilaiEnum(
                            $this->resource
                                ->tingkat_urgensi
                        ),

                    'label' =>
                        $this->labelEnum(
                            $this->resource
                                ->tingkat_urgensi
                        ),
                ],

                'dibutuhkan_pada' =>
                    $this->formatTanggal(
                        $this->resource
                            ->dibutuhkan_pada
                    ),
            ],

            'dokumen_permintaan_url' =>
                filled($pathDokumen)
                    ? asset(
                        'storage/' . ltrim(
                            (string) $pathDokumen,
                            '/'
                        )
                    )
                    : null,

            'status' => [
                'value' =>
                    $this->nilaiEnum(
                        $this->resource->status
                    ),

                'label' =>
                    $this->labelEnum(
                        $this->resource->status
                    ),
            ],

            'proses' => [
                'ditinjau_oleh' =>
                    $peninjau?->name,

                'ditinjau_pada' =>
                    $this->formatTanggal(
                        $this->resource
                            ->ditinjau_pada
                    ),

                'disetujui_pada' =>
                    $this->formatTanggal(
                        $this->resource
                            ->disetujui_pada
                    ),

                'siap_diambil_pada' =>
                    $this->formatTanggal(
                        $this->resource
                            ->siap_diambil_pada
                    ),

                'selesai_pada' =>
                    $this->formatTanggal(
                        $this->resource
                            ->selesai_pada
                    ),

                'dibatalkan_pada' =>
                    $this->formatTanggal(
                        $this->resource
                            ->dibatalkan_pada
                    ),
            ],

            'alasan_penolakan' =>
                $this->resource
                    ->alasan_penolakan,

            'alasan_pembatalan' =>
                $this->resource
                    ->alasan_pembatalan,

            'catatan' =>
                $this->resource->catatan,

            'hak_aksi' => [
                'dapat_diubah' =>
                    $this->resource
                        ->dapatDiubah(),

                'dapat_dibatalkan' =>
                    $this->resource
                        ->dapatDibatalkan(),
            ],

            'dibuat_pada' =>
                $this->formatTanggal(
                    $this->resource->created_at
                ),

            'diperbarui_pada' =>
                $this->formatTanggal(
                    $this->resource->updated_at
                ),
        ];
    }

    private function formatTanggal(
        mixed $tanggal
    ): ?string {
        return $tanggal instanceof CarbonInterface
            ? $tanggal->toIso8601String()
            : null;
    }

    private function nilaiEnum(
        mixed $nilai
    ): ?string {
        if ($nilai instanceof BackedEnum) {
            return (string) $nilai->value;
        }

        return filled($nilai)
            ? (string) $nilai
            : null;
    }

    private function labelEnum(
        mixed $nilai
    ): ?string {
        if (
            is_object($nilai)
            && method_exists($nilai, 'label')
        ) {
            return (string) $nilai->label();
        }

        $value = $this->nilaiEnum($nilai);

        return filled($value)
            ? Str::headline($value)
            : null;
    }
}