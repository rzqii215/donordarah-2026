<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Api\Handlers;

use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Filament\Admin\Resources\DistribusiDarahResource\Api\Transformers\DistribusiDarahTransformer;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class DetailHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/{id}';

    public static string|null $resource =
        DistribusiDarahResource::class;

    /**
     * Detail distribusi darah milik Rumah Sakit yang sedang login.
     */
    public function handler(
        Request $request
    ) {
        $profilRumahSakit = $this->profilRumahSakit(
            request: $request,
            harusTerverifikasi: true,
        );

        $record = static::getEloquentQuery()
            ->with([
                'permintaan.rumahSakit',
            ])
            ->whereHas(
                'permintaan',
                function (
                    Builder $query
                ) use (
                    $profilRumahSakit
                ): void {
                    $query->where(
                        'profil_rumah_sakit_id',
                        $profilRumahSakit->id
                    );
                }
            )
            ->where(
                static::getKeyName(),
                $request->route('id')
            )
            ->first();

        if ($record === null) {
            return static::sendNotFoundResponse();
        }

        return new DistribusiDarahTransformer(
            $record
        );
    }
}