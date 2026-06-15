<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Requests\CreatePendaftaranDonorRequest;
use App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Transformers\PendaftaranDonorTransformer;
use App\Services\LayananPendaftaranDonor;
use App\Support\Api\MemastikanPendonorTerautentikasi;
use Illuminate\Http\JsonResponse;
use Rupadana\ApiService\Http\Handlers;

class CreateHandler extends Handlers
{
    use MemastikanPendonorTerautentikasi;

    public static string|null $uri = '/';

    public static string|null $resource =
        PendaftaranDonorResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    /**
     * Membuat pendaftaran donor untuk pengguna yang login.
     */
    public function handler(
        CreatePendaftaranDonorRequest $request
    ): JsonResponse {
        $pengguna = $this->penggunaPendonor(
            $request
        );

        $data = $request->validated();

        $record = app(
            LayananPendaftaranDonor::class
        )->daftar(
            jadwalDonorId:
                (int) $data['jadwal_donor_id'],

            pendonorId:
                $pengguna->id,

            data: [
                'jawaban_skrining' =>
                    $data['jawaban_skrining']
                    ?? null,

                'catatan' =>
                    $data['catatan']
                    ?? null,
            ],
        );

        $record->load('jadwal');

        return (
            new PendaftaranDonorTransformer(
                $record
            )
        )
            ->response()
            ->setStatusCode(201);
    }
}