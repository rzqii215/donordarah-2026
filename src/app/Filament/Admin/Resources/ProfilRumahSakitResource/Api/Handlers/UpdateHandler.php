<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Handlers;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Requests\UpdateProfilRumahSakitRequest;
use App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Transformers\ProfilRumahSakitTransformer;
use App\Services\LayananProfilRumahSakit;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Rupadana\ApiService\Http\Handlers;

class UpdateHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/me';

    public static string|null $resource =
        ProfilRumahSakitResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    /**
     * Memperbarui profil Rumah Sakit yang sedang login.
     */
    public function handler(
        UpdateProfilRumahSakitRequest $request
    ): ProfilRumahSakitTransformer {
        $pengguna = $this->penggunaRumahSakit(
            $request
        );

        $profil = app(
            LayananProfilRumahSakit::class
        )->perbaruiMilikPengguna(
            pengguna: $pengguna,
            data: $request->validated(),
        );

        return new ProfilRumahSakitTransformer(
            $profil
        );
    }
}