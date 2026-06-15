<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Api;

use App\Filament\Admin\Resources\LokasiDonorResource;
use App\Filament\Admin\Resources\LokasiDonorResource\Api\Handlers\DetailHandler;
use App\Filament\Admin\Resources\LokasiDonorResource\Api\Handlers\PaginationHandler;
use Rupadana\ApiService\ApiService;

class LokasiDonorApiService extends ApiService
{
    protected static string|null $resource =
        LokasiDonorResource::class;

    /**
     * API Lokasi Donor hanya menyediakan operasi baca.
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