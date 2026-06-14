<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Api;

use App\Filament\Admin\Resources\JadwalDonorResource;
use Rupadana\ApiService\ApiService;

class JadwalDonorApiService extends ApiService
{
    protected static string|null $resource =
        JadwalDonorResource::class;

    /**
     * API Jadwal Donor hanya menyediakan operasi baca.
     *
     * @return array<class-string>
     */
    public static function handlers(): array
    {
        return [
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class,
        ];
    }
}