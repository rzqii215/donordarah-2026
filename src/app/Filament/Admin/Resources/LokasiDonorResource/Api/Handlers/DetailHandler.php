<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\LokasiDonorResource;
use App\Filament\Admin\Resources\LokasiDonorResource\Api\Transformers\LokasiDonorTransformer;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class DetailHandler extends Handlers
{
    public static string|null $uri = '/{id}';

    public static string|null $resource =
        LokasiDonorResource::class;

    /**
     * Endpoint dapat diakses tanpa token Sanctum.
     */
    public static bool $public = true;

    /**
     * Detail lokasi donor.
     *
     * @return LokasiDonorTransformer|\Illuminate\Http\JsonResponse
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');

        $record = static::getEloquentQuery()
            ->where(
                static::getKeyName(),
                $id
            )
            ->first();

        if ($record === null) {
            return static::sendNotFoundResponse();
        }

        return new LokasiDonorTransformer(
            $record
        );
    }
}