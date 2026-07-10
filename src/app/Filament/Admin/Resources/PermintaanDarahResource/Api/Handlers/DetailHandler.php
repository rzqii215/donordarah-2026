<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Transformers\PermintaanDarahTransformer;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class DetailHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/{id}';

    public static string|null $resource =
        PermintaanDarahResource::class;

    /**
     * Detail pengajuan kebutuhan donor milik Pemohon Donor yang sedang login.
     */
    public function handler(
        Request $request
    ) {
        $profil = $this->profilRumahSakit(
            request: $request,
            harusTerverifikasi: true,
        );

        $record = static::getEloquentQuery()
            ->with([
                'rumahSakit',
                'peninjau',
            ])
            ->where(
                'profil_rumah_sakit_id',
                $profil->id
            )
            ->where(
                static::getKeyName(),
                $request->route('id')
            )
            ->first();

        if ($record === null) {
            return static::sendNotFoundResponse();
        }

        return new PermintaanDarahTransformer(
            $record
        );
    }
}