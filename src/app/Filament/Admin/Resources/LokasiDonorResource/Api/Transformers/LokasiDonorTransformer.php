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
    public function toArray(
        Request $request
    ): array {
        $kodeLokasi = $this->atribut([
            'kode_lokasi',
            'kode',
        ]);

        $namaLokasi = $this->atribut([
            'nama',
            'nama_lokasi',
            'judul',
        ]);

        $jenisLokasi = $this->atribut([
            'jenis_lokasi',
            'jenis',
        ]);

        $alamat = $this->atribut([
            'alamat',
            'alamat_lengkap',
        ]);

        $provinsi = $this->atribut([
            'provinsi',
        ]);

        $kota = $this->atribut([
            'kota',
            'kabupaten_kota',
            'kabupaten',
        ]);

        $kecamatan = $this->atribut([
            'kecamatan',
        ]);

        $kodePos = $this->atribut([
            'kode_pos',
        ]);

        $latitude = $this->atribut([
            'latitude',
            'lat',
        ]);

        $longitude = $this->atribut([
            'longitude',
            'lng',
            'lon',
        ]);

        $nomorTelepon = $this->atribut([
            'nomor_telepon',
            'telepon',
            'no_telepon',
        ]);

        $email = $this->atribut([
            'email',
        ]);

        $gambar = $this->atribut([
            'path_gambar',
            'gambar',
            'foto',
            'image',
        ]);

        $status = $this->atribut([
            'status',
        ]);

        return [
            'id' => $this->resource->getKey(),

            'kode_lokasi' => $kodeLokasi,

            'nama_lokasi' => $namaLokasi,

            'jenis_lokasi' => $jenisLokasi,

            'deskripsi' => $this->atribut([
                'deskripsi',
                'keterangan',
            ]),

            'gambar_url' => filled($gambar)
                ? asset(
                    'storage/' . ltrim(
                        (string) $gambar,
                        '/'
                    )
                )
                : null,

            'alamat' => [
                'lengkap' => $alamat,
                'provinsi' => $provinsi,
                'kota' => $kota,
                'kecamatan' => $kecamatan,
                'kode_pos' => $kodePos,
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
                    $nomorTelepon,

                'email' =>
                    $email,
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
                    $this->atribut([
                        'created_at',
                    ])
                ),

            'diperbarui_pada' =>
                $this->formatTanggal(
                    $this->atribut([
                        'updated_at',
                    ])
                ),
        ];
    }

    /**
     * Mengambil nilai dari nama atribut pertama yang tersedia.
     *
     * @param array<int, string> $candidates
     */
    private function atribut(
        array $candidates
    ): mixed {
        $attributes = $this->resource
            ->getAttributes();

        foreach ($candidates as $candidate) {
            if (
                array_key_exists(
                    $candidate,
                    $attributes
                )
            ) {
                return $this->resource
                    ->getAttribute(
                        $candidate
                    );
            }
        }

        return null;
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
        if ($tanggal instanceof CarbonInterface) {
            return $tanggal->toIso8601String();
        }

        if (blank($tanggal)) {
            return null;
        }

        try {
            return \Carbon\CarbonImmutable::parse(
                (string) $tanggal
            )->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
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
}