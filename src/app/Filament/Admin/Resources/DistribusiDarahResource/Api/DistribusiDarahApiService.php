<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Api;

use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Filament\Admin\Resources\DistribusiDarahResource\Api\Handlers\DetailHandler;
use App\Filament\Admin\Resources\DistribusiDarahResource\Api\Handlers\PaginationHandler;
use Rupadana\ApiService\ApiService;

class DistribusiDarahApiService extends ApiService
{
    protected static string|null $resource =
        DistribusiDarahResource::class;

    /**
     * API Distribusi Darah hanya menyediakan operasi baca
     * untuk Rumah Sakit pemilik permintaan.
     *
     * @return array<class-string>
     */
    public static function handlers(): array
    {
        return [
            PaginationHandler::class,
            DetailHandler::class,
        ];
    }
}