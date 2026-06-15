<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api;

use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use Rupadana\ApiService\ApiService;

class ProfilRumahSakitApiService extends ApiService
{
    protected static string|null $resource =
        ProfilRumahSakitResource::class;

    /**
     * @return array<class-string>
     */
    public static function handlers(): array
    {
        return [
            Handlers\DetailHandler::class,
            Handlers\UpdateHandler::class,
        ];
    }
}