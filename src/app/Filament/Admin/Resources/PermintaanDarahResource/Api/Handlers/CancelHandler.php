<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Requests\CancelPermintaanDarahRequest;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Transformers\PermintaanDarahTransformer;
use App\Models\PermintaanDarah;
use App\Services\LayananPermintaanDarah;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Illuminate\Http\JsonResponse;
use Rupadana\ApiService\Http\Handlers;

class CancelHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/{id}/cancel';

    public static string|null $resource =
        PermintaanDarahResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    /**
     * Membatalkan permintaan darah milik Rumah Sakit yang sedang login.
     */
    public function handler(
        CancelPermintaanDarahRequest $request
    ): JsonResponse {
        $profilRumahSakit = $this->profilRumahSakit(
            request: $request,
            harusTerverifikasi: true,
        );

        $permintaanDarah = PermintaanDarah::query()
            ->where(
                'profil_rumah_sakit_id',
                $profilRumahSakit->id
            )
            ->whereKey(
                $request->route('id')
            )
            ->first();

        if ($permintaanDarah === null) {
            return static::sendNotFoundResponse();
        }

        $data = $request->validated();

        $record = app(
            LayananPermintaanDarah::class
        )->batalkan(
            permintaan: $permintaanDarah,
            alasan: (string) $data['alasan'],
        );

        $record->load([
            'rumahSakit',
            'peninjau',
        ]);

        return (
            new PermintaanDarahTransformer(
                $record
            )
        )->response();
    }
}