<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Api\Transformers;

use App\Models\LokasiDonor;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property LokasiDonor $resource
 */
class LokasiDonorTransformer extends JsonResource
{
    /**
     * Mengubah Lokasi Donor menjadi response API publik.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gambar = $this->resource->getAttribute(
            'path_gambar'
        );

        $status = $this->resource->getAttribute(
            'status'
        );

        $latitude = $this->resource->getAttribute(
            'latitude'
        );

        $longitude = $this->resource->getAttribute(
            'longitude'
        );

        return [
            'id' => $this->resource->getKey(),

            'kode_lokasi' =>
                $this->resource->getAttribute(
                    'kode_lokasi'
                ),

            'nama_lokasi' =>
                $this->resource->getAttribute(
                    'nama_lokasi'
                ),

            'jenis_lokasi' =>
                $this->resource->getAttribute(
                    'jenis_lokasi'
                ),

            'deskripsi' =>
                $this->resource->getAttribute(
                    'deskripsi'
                ),

            'gambar_url' => filled($gambar)
                ? asset(
                    'storage/' . ltrim(
                        (string) $gambar,
                        '/'
                    )
                )
                : null,

            'alamat' => [
                'lengkap' =>
                    $this->resource->getAttribute(
                        'alamat'
                    ),

                'provinsi' =>
                    $this->resource->getAttribute(
                        'provinsi'
                    ),

                'kota' =>
                    $this->resource->getAttribute(
                        'kota'
                    ),

                'kecamatan' =>
                    $this->resource->getAttribute(
                        'kecamatan'
                    ),

                'kode_pos' =>
                    $this->resource->getAttribute(
                        'kode_pos'
                    ),
            ],

            'koordinat' => [
                'latitude' =>
                    $this->nilaiDesimal(
                        $latitude
                    ),

                'longitude' =>
                    $this->nilaiDesimal(
                        $longitude
                    ),

                'tersedia' =>
                    filled($latitude)
                    && filled($longitude),
            ],

            'kontak' => [
                'nomor_telepon' =>
                    $this->resource->getAttribute(
                        'nomor_telepon'
                    ),

                'email' =>
                    $this->resource->getAttribute(
                        'email'
                    ),
            ],

            'status' => [
                'value' =>
                    $this->nilaiEnum(
                        $status
                    ),

                'label' =>
                    $this->labelEnum(
                        $status
                    ),
            ],

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

    private function nilaiDesimal(
        mixed $nilai
    ): ?float {
        if (
            $nilai === null
            || $nilai === ''
        ) {
            return null;
        }

        return (float) $nilai;
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
            && method_exists(
                $nilai,
                'label'
            )
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