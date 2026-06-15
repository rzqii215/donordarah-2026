<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers\CancelHandler;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers\CreateHandler;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers\DetailHandler;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers\PaginationHandler;
use Rupadana\ApiService\ApiService;

class PermintaanDarahApiService extends ApiService
{
    protected static string|null $resource =
        PermintaanDarahResource::class;

    /**
     * @return array<class-string>
     */
    public static function handlers(): array
    {
        return [
            CreateHandler::class,
            PaginationHandler::class,
            CancelHandler::class,
            DetailHandler::class,
        ];
    }
}