<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\JadwalDonorResource;
use App\Filament\Admin\Resources\JadwalDonorResource\Api\Transformers\JadwalDonorTransformer;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class DetailHandler extends Handlers
{
    public static string|null $uri = '/{id}';

    public static string|null $resource =
        JadwalDonorResource::class;

    /**
     * Endpoint dapat diakses tanpa token Sanctum.
     */
    public static bool $public = true;

    /**
     * Detail jadwal donor yang sudah dipublikasikan.
     *
     * @return JadwalDonorTransformer|\Illuminate\Http\JsonResponse
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');

        $record = static::getEloquentQuery()
            ->where('status', 'published')
            ->where(
                static::getKeyName(),
                $id
            )
            ->first();

        if ($record === null) {
            return static::sendNotFoundResponse();
        }

        return new JadwalDonorTransformer(
            $record
        );
    }
}