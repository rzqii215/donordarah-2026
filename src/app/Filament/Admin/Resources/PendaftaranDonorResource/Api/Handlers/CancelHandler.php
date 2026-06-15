<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Requests\CancelPendaftaranDonorRequest;
use App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Transformers\PendaftaranDonorTransformer;
use App\Models\PendaftaranDonor;
use App\Services\LayananPendaftaranDonor;
use App\Support\Api\MemastikanPendonorTerautentikasi;
use Illuminate\Http\JsonResponse;
use Rupadana\ApiService\Http\Handlers;

class CancelHandler extends Handlers
{
    use MemastikanPendonorTerautentikasi;

    public static string|null $uri = '/{id}/cancel';

    public static string|null $resource =
        PendaftaranDonorResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    /**
     * Membatalkan pendaftaran donor milik pengguna yang sedang login.
     */
    public function handler(
        CancelPendaftaranDonorRequest $request
    ): JsonResponse {
        $pengguna = $this->penggunaPendonor(
            $request
        );

        $pendaftaran = PendaftaranDonor::query()
            ->where(
                'pendonor_id',
                $pengguna->id
            )
            ->whereKey(
                $request->route('id')
            )
            ->first();

        if ($pendaftaran === null) {
            return static::sendNotFoundResponse();
        }

        $data = $request->validated();

        $record = app(
            LayananPendaftaranDonor::class
        )->batalkan(
            pendaftaran: $pendaftaran,
            alasan: (string) $data['alasan'],
        );

        $record->load('jadwal');

        return (
            new PendaftaranDonorTransformer(
                $record
            )
        )->response();
    }
}