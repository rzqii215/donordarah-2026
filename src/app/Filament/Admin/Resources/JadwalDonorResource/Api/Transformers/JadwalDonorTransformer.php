<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Api\Transformers;

use App\Models\JadwalDonor;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property JadwalDonor $resource
 */
class JadwalDonorTransformer extends JsonResource
{
    /**
     * Mengubah Jadwal Donor menjadi response API publik.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->resource->getAttribute('status');

        $pendaftaranDibukaPada = $this->resource->getAttribute(
            'pendaftaran_dibuka_pada'
        );

        $pendaftaranDitutupPada = $this->resource->getAttribute(
            'pendaftaran_ditutup_pada'
        );

        $gambar = $this->resource->getAttribute('path_gambar');

        return [
            'id' => $this->resource->getKey(),

            'kode_jadwal' => $this->resource->getAttribute(
                'kode_jadwal'
            ),

            'judul' => $this->resource->getAttribute(
                'judul'
            ),

            'deskripsi' => $this->resource->getAttribute(
                'deskripsi'
            ),

            'lokasi_donor_id' => (int) $this->resource->getAttribute(
                'lokasi_donor_id'
            ),

            'gambar_url' => filled($gambar)
                ? asset(
                    'storage/' . ltrim(
                        (string) $gambar,
                        '/'
                    )
                )
                : null,

            'kegiatan' => [
                'mulai_pada' => $this->formatTanggal(
                    $this->resource->getAttribute(
                        'mulai_pada'
                    )
                ),

                'selesai_pada' => $this->formatTanggal(
                    $this->resource->getAttribute(
                        'selesai_pada'
                    )
                ),
            ],

            'pendaftaran' => [
                'dibuka_pada' => $this->formatTanggal(
                    $pendaftaranDibukaPada
                ),

                'ditutup_pada' => $this->formatTanggal(
                    $pendaftaranDitutupPada
                ),

                'sedang_dibuka' => $this->pendaftaranSedangDibuka(
                    dibukaPada: $pendaftaranDibukaPada,
                    ditutupPada: $pendaftaranDitutupPada,
                ),

                'kuota_pendonor' => (int) $this->resource->getAttribute(
                    'kuota_pendonor'
                ),
            ],

            'status' => [
                'value' => $this->nilaiEnum(
                    $status
                ),

                'label' => $this->labelEnum(
                    $status
                ),
            ],

            'dibuat_pada' => $this->formatTanggal(
                $this->resource->getAttribute(
                    'created_at'
                )
            ),

            'diperbarui_pada' => $this->formatTanggal(
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

    private function pendaftaranSedangDibuka(
        mixed $dibukaPada,
        mixed $ditutupPada
    ): bool {
        if (
            ! $dibukaPada instanceof CarbonInterface
            || ! $ditutupPada instanceof CarbonInterface
        ) {
            return false;
        }

        $sekarang = now();

        return $sekarang->greaterThanOrEqualTo(
            $dibukaPada
        ) && $sekarang->lessThanOrEqualTo(
            $ditutupPada
        );
    }

    private function nilaiEnum(
        mixed $nilai
    ): ?string {
        if ($nilai instanceof BackedEnum) {
            return (string) $nilai->value;
        }

        if (blank($nilai)) {
            return null;
        }

        return (string) $nilai;
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

        $nilaiEnum = $this->nilaiEnum(
            $nilai
        );

        return filled($nilaiEnum)
            ? Str::headline($nilaiEnum)
            : null;
    }
}