<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Api\Transformers;

use App\Models\DistribusiDarah;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property DistribusiDarah $resource
 */
class DistribusiDarahTransformer extends JsonResource
{
    /**
     * Mengubah Distribusi Kantong Darah menjadi response API Pemohon Donor.
     *
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        $permintaan = $this->resource
            ->getRelationValue('permintaan');

        $pemohonDonor = $permintaan
            ?->getRelationValue('rumahSakit');

        return [
            'id' => $this->resource->getKey(),

            'nomor_distribusi' =>
                $this->resource->getAttribute(
                    'nomor_distribusi'
                ),

            'pengajuan' => [
                'id' =>
                    $permintaan?->getKey(),

                'nomor_pengajuan' =>
                    $permintaan?->getAttribute(
                        'nomor_permintaan'
                    ),

                'referensi_pengajuan' =>
                    $permintaan?->getAttribute(
                        'referensi_pasien'
                    ),

                'golongan_darah' => [
                    'value' =>
                        $this->nilaiEnum(
                            $permintaan?->getAttribute(
                                'golongan_darah'
                            )
                        ),

                    'label' =>
                        $this->labelEnum(
                            $permintaan?->getAttribute(
                                'golongan_darah'
                            )
                        ),
                ],

                'rhesus' => [
                    'value' =>
                        $this->nilaiEnum(
                            $permintaan?->getAttribute(
                                'rhesus'
                            )
                        ),

                    'label' =>
                        $this->labelEnum(
                            $permintaan?->getAttribute(
                                'rhesus'
                            )
                        ),

                    'simbol' =>
                        $this->simbolRhesus(
                            $permintaan?->getAttribute(
                                'rhesus'
                            )
                        ),
                ],

                'jumlah_kantong' =>
                    $permintaan !== null
                        ? (int) $permintaan->getAttribute(
                            'jumlah_kantong'
                        )
                        : 0,

                'status' => [
                    'value' =>
                        $this->nilaiEnum(
                            $permintaan?->getAttribute(
                                'status'
                            )
                        ),

                    'label' =>
                        $this->labelEnum(
                            $permintaan?->getAttribute(
                                'status'
                            )
                        ),
                ],
            ],

            'pemohon_donor' => [
                'id' =>
                    $pemohonDonor?->getKey(),

                'kode' =>
                    $pemohonDonor?->getAttribute(
                        'kode_rumah_sakit'
                    ),

                'nama' =>
                    $pemohonDonor?->getAttribute(
                        'nama_rumah_sakit'
                    ),
            ],

            'status' => [
                'value' =>
                    $this->nilaiEnum(
                        $this->resource->getAttribute(
                            'status'
                        )
                    ),

                'label' =>
                    $this->labelEnum(
                        $this->resource->getAttribute(
                            'status'
                        )
                    ),
            ],

            'jadwal' => [
                'dijadwalkan_pada' =>
                    $this->formatTanggal(
                        $this->resource->getAttribute(
                            'dijadwalkan_pada'
                        )
                    ),

                'diserahkan_pada' =>
                    $this->formatTanggal(
                        $this->resource->getAttribute(
                            'diserahkan_pada'
                        )
                    ),
            ],

            'penerima' => [
                'nama' =>
                    $this->resource->getAttribute(
                        'nama_penerima'
                    ),

                'jabatan' =>
                    $this->resource->getAttribute(
                        'jabatan_penerima'
                    ),

                'nomor_identitas' =>
                    $this->resource->getAttribute(
                        'nomor_identitas_penerima'
                    ),
            ],

            'bukti_serah_terima' =>
                $this->resource->getAttribute(
                    'path_bukti_serah_terima'
                ),

            'catatan' =>
                $this->resource->getAttribute(
                    'catatan'
                ),

            'alasan_pembatalan' =>
                $this->resource->getAttribute(
                    'alasan_pembatalan'
                ),

            'dibuat_pada' =>
                $this->formatTanggal(
                    $this->resource->getAttribute(
                        'created_at'
                    )
                ),

            'diperbarui_pada' =>
                $this->formatTanggal(
                    $this->resource->getAttribute(
                        'updated_at'
                    )
                ),
        ];
    }

    private function formatTanggal(
        mixed $tanggal
    ): ?string {
        if (! $tanggal instanceof CarbonInterface) {
            return null;
        }

        return $tanggal->toIso8601String();
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
            && method_exists(
                $nilai,
                'label'
            )
        ) {
            return (string) $nilai->label();
        }

        $value = $this->nilaiEnum(
            $nilai
        );

        return filled($value)
            ? Str::headline($value)
            : null;
    }

    private function simbolRhesus(
        mixed $nilai
    ): ?string {
        if (
            is_object($nilai)
            && method_exists(
                $nilai,
                'simbol'
            )
        ) {
            return (string) $nilai->simbol();
        }

        return null;
    }
}