<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Api;

use App\Filament\Admin\Resources\ProfilPendonorResource;
use Rupadana\ApiService\ApiService;

class ProfilPendonorApiService extends ApiService
{
    protected static string|null $resource =
        ProfilPendonorResource::class;

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