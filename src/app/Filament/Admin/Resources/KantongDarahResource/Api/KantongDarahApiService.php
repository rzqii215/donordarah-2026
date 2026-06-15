<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Api;

use App\Filament\Admin\Resources\KantongDarahResource;
use Rupadana\ApiService\ApiService;

class KantongDarahApiService extends ApiService
{
    protected static string|null $resource =
        KantongDarahResource::class;

    /**
     * API publik hanya menyediakan ringkasan stok darah.
     *
     * @return array<class-string>
     */
    public static function handlers(): array
    {
        return [
            Handlers\PaginationHandler::class,
        ];
    }
}