<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Transformers;

use App\Models\ProfilRumahSakit;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property ProfilRumahSakit $resource
 */
class ProfilRumahSakitTransformer extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        $pengguna = $this->resource->pengguna;
        $verifikator = $this->resource->verifikator;

        $dokumenIzin = $this->resource->getAttribute(
            'path_dokumen_izin'
        );

        return [
            'id' => $this->resource->getKey(),

            'kode_rumah_sakit' =>
                $this->resource
                    ->kode_rumah_sakit,

            'nama_rumah_sakit' =>
                $this->resource
                    ->nama_rumah_sakit,

            'nomor_izin' =>
                $this->resource
                    ->nomor_izin,

            'dokumen_izin_url' =>
                filled($dokumenIzin)
                    ? asset(
                        'storage/' . ltrim(
                            (string) $dokumenIzin,
                            '/'
                        )
                    )
                    : null,

            'akun' => [
                'id' =>
                    $pengguna?->getKey(),

                'nama' =>
                    $pengguna?->name,

                'email' =>
                    $pengguna?->email,

                'nomor_telepon' =>
                    $pengguna?->nomor_telepon,

                'avatar_url' =>
                    $pengguna
                        ?->getFilamentAvatarUrl(),

                'status' => [
                    'value' =>
                        $this->nilaiEnum(
                            $pengguna?->status
                        ),

                    'label' =>
                        $this->labelEnum(
                            $pengguna?->status
                        ),
                ],
            ],

            'penanggung_jawab' => [
                'nama' =>
                    $this->resource
                        ->nama_penanggung_jawab,

                'jabatan' =>
                    $this->resource
                        ->jabatan_penanggung_jawab,
            ],

            'alamat' => [
                'lengkap' =>
                    $this->resource->alamat,

                'provinsi' =>
                    $this->resource->provinsi,

                'kota' =>
                    $this->resource->kota,

                'kecamatan' =>
                    $this->resource->kecamatan,

                'kode_pos' =>
                    $this->resource->kode_pos,
            ],

            'koordinat' => [
                'latitude' =>
                    $this->nilaiDesimal(
                        $this->resource->latitude
                    ),

                'longitude' =>
                    $this->nilaiDesimal(
                        $this->resource->longitude
                    ),

                'tersedia' =>
                    filled($this->resource->latitude)
                    && filled(
                        $this->resource->longitude
                    ),
            ],

            'verifikasi' => [
                'status' => [
                    'value' =>
                        $this->nilaiEnum(
                            $this->resource
                                ->status_verifikasi
                        ),

                    'label' =>
                        $this->labelEnum(
                            $this->resource
                                ->status_verifikasi
                        ),
                ],

                'diverifikasi_oleh' =>
                    $verifikator?->name,

                'diverifikasi_pada' =>
                    $this->formatTanggal(
                        $this->resource
                            ->diverifikasi_pada
                    ),

                'alasan_penolakan' =>
                    $this->resource
                        ->alasan_penolakan,
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