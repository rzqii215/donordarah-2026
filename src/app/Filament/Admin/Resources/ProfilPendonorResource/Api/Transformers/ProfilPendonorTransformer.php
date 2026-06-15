<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Api\Transformers;

use App\Models\ProfilPendonor;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property ProfilPendonor $resource
 */
class ProfilPendonorTransformer extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        $pengguna = $this->resource->pengguna;

        return [
            'id' => $this->resource->getKey(),

            'kode_pendonor' =>
                $this->resource->kode_pendonor,

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
                    $pengguna?->getFilamentAvatarUrl(),

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

            'tanggal_lahir' =>
                $this->formatTanggal(
                    $this->resource->tanggal_lahir,
                    false
                ),

            'jenis_kelamin' => [
                'value' =>
                    $this->nilaiEnum(
                        $this->resource->jenis_kelamin
                    ),

                'label' =>
                    $this->labelEnum(
                        $this->resource->jenis_kelamin
                    ),
            ],

            'golongan_darah' => [
                'value' =>
                    $this->nilaiEnum(
                        $this->resource->golongan_darah
                    ),

                'label' =>
                    $this->labelEnum(
                        $this->resource->golongan_darah
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
                    is_object($this->resource->rhesus)
                    && method_exists(
                        $this->resource->rhesus,
                        'simbol'
                    )
                        ? $this->resource
                            ->rhesus
                            ->simbol()
                        : null,
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

            'kontak_darurat' => [
                'nama' =>
                    $this->resource
                        ->nama_kontak_darurat,

                'telepon' =>
                    $this->resource
                        ->telepon_kontak_darurat,
            ],

            'terakhir_donor_pada' =>
                $this->formatTanggal(
                    $this->resource
                        ->terakhir_donor_pada
                ),

            'bersedia_dihubungi' =>
                (bool) $this->resource
                    ->bersedia_dihubungi,

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
        mixed $tanggal,
        bool $denganWaktu = true
    ): ?string {
        if (! $tanggal instanceof CarbonInterface) {
            return null;
        }

        return $denganWaktu
            ? $tanggal->toIso8601String()
            : $tanggal->toDateString();
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