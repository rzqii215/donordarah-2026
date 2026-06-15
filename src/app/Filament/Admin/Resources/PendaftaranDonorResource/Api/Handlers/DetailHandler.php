<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Transformers\PendaftaranDonorTransformer;
use App\Support\Api\MemastikanPendonorTerautentikasi;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class DetailHandler extends Handlers
{
    use MemastikanPendonorTerautentikasi;

    public static string|null $uri = '/{id}';

    public static string|null $resource =
        PendaftaranDonorResource::class;

    /**
     * Detail pendaftaran milik Pendonor yang sedang login.
     */
    public function handler(
        Request $request
    ) {
        $pengguna = $this->penggunaPendonor(
            $request
        );

        $record = static::getEloquentQuery()
            ->with('jadwal')
            ->where(
                'pendonor_id',
                $pengguna->id
            )
            ->where(
                static::getKeyName(),
                $request->route('id')
            )
            ->first();

        if ($record === null) {
            return static::sendNotFoundResponse();
        }

        return new PendaftaranDonorTransformer(
            $record
        );
    }
}