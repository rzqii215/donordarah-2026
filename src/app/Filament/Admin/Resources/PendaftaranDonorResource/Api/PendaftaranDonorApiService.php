<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use Rupadana\ApiService\ApiService;

class PendaftaranDonorApiService extends ApiService
{
    protected static string|null $resource =
        PendaftaranDonorResource::class;

    /**
     * @return array<class-string>
     */
    public static function handlers(): array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\CancelHandler::class,
            Handlers\DetailHandler::class,
        ];
    }
}