<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Transformers;

use App\Models\PendaftaranDonor;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property PendaftaranDonor $resource
 */
class PendaftaranDonorTransformer extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        $jadwal = $this->resource->jadwal;

        return [
            'id' => $this->resource->getKey(),

            'nomor_pendaftaran' =>
                $this->resource
                    ->nomor_pendaftaran,

            'jadwal' => [
                'id' =>
                    $jadwal?->getKey(),

                'kode_jadwal' =>
                    $jadwal?->kode_jadwal,

                'judul' =>
                    $jadwal?->judul,

                'lokasi_donor_id' =>
                    $jadwal?->lokasi_donor_id,

                'mulai_pada' =>
                    $this->formatTanggal(
                        $jadwal?->mulai_pada
                    ),

                'selesai_pada' =>
                    $this->formatTanggal(
                        $jadwal?->selesai_pada
                    ),
            ],

            'jawaban_skrining' =>
                $this->resource
                    ->jawaban_skrining,

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

            'ditinjau_pada' =>
                $this->formatTanggal(
                    $this->resource
                        ->ditinjau_pada
                ),

            'alasan_penolakan' =>
                $this->resource
                    ->alasan_penolakan,

            'hadir_pada' =>
                $this->formatTanggal(
                    $this->resource
                        ->hadir_pada
                ),

            'dibatalkan_pada' =>
                $this->formatTanggal(
                    $this->resource
                        ->dibatalkan_pada
                ),

            'alasan_pembatalan' =>
                $this->resource
                    ->alasan_pembatalan,

            'selesai_pada' =>
                $this->formatTanggal(
                    $this->resource
                        ->selesai_pada
                ),

            'catatan' =>
                $this->resource->catatan,

            'dapat_dibatalkan' =>
                $this->resource
                    ->dapatDibatalkan(),

            'dibuat_pada' =>
                $this->formatTanggal(
                    $this->resource
                        ->created_at
                ),

            'diperbarui_pada' =>
                $this->formatTanggal(
                    $this->resource
                        ->updated_at
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