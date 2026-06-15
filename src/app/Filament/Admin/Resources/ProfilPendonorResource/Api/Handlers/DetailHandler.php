<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Api\Handlers;

use App\Filament\Admin\Resources\ProfilPendonorResource;
use App\Filament\Admin\Resources\ProfilPendonorResource\Api\Transformers\ProfilPendonorTransformer;
use App\Models\ProfilPendonor;
use App\Support\Api\MemastikanPendonorTerautentikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Rupadana\ApiService\Http\Handlers;

class DetailHandler extends Handlers
{
    use MemastikanPendonorTerautentikasi;

    public static string|null $uri = '/me';

    public static string|null $resource =
        ProfilPendonorResource::class;

    /**
     * Menampilkan profil Pendonor yang sedang login.
     */
    public function handler(
        Request $request
    ): ProfilPendonorTransformer {
        $pengguna = $this->penggunaPendonor(
            $request
        );

        $profil = ProfilPendonor::query()
            ->with('pengguna')
            ->where(
                'pengguna_id',
                $pengguna->id
            )
            ->first();

        if ($profil === null) {
            throw ValidationException::withMessages([
                'profil' =>
                    'Profil Pendonor belum tersedia.',
            ]);
        }

        return new ProfilPendonorTransformer(
            $profil
        );
    }
}