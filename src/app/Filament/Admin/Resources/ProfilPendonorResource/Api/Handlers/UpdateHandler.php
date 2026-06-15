<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Api\Handlers;

use App\Filament\Admin\Resources\ProfilPendonorResource;
use App\Filament\Admin\Resources\ProfilPendonorResource\Api\Requests\UpdateProfilPendonorRequest;
use App\Filament\Admin\Resources\ProfilPendonorResource\Api\Transformers\ProfilPendonorTransformer;
use App\Services\LayananProfilPendonor;
use App\Support\Api\MemastikanPendonorTerautentikasi;
use Rupadana\ApiService\Http\Handlers;

class UpdateHandler extends Handlers
{
    use MemastikanPendonorTerautentikasi;

    public static string|null $uri = '/me';

    public static string|null $resource =
        ProfilPendonorResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    /**
     * Memperbarui profil Pendonor yang sedang login.
     */
    public function handler(
        UpdateProfilPendonorRequest $request
    ): ProfilPendonorTransformer {
        $pengguna = $this->penggunaPendonor(
            $request
        );

        $profil = app(
            LayananProfilPendonor::class
        )->perbaruiMilikPengguna(
            pengguna: $pengguna,
            data: $request->validated(),
        );

        return new ProfilPendonorTransformer(
            $profil
        );
    }
}