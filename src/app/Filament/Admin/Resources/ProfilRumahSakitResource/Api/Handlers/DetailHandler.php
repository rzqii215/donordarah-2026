<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Handlers;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Transformers\ProfilRumahSakitTransformer;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class DetailHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/me';

    public static string|null $resource =
        ProfilRumahSakitResource::class;

    /**
     * Menampilkan profil Pemohon Donor yang sedang login.
     */
    public function handler(
        Request $request
    ): ProfilRumahSakitTransformer {
        $profil = $this->profilRumahSakit(
            request: $request,
            harusTerverifikasi: false,
        );

        $profil->load([
            'pengguna',
            'verifikator',
        ]);

        return new ProfilRumahSakitTransformer(
            $profil
        );
    }
}