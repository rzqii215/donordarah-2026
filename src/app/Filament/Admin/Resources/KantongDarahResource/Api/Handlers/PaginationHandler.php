<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Api\Handlers;

use App\Filament\Admin\Resources\KantongDarahResource;
use App\Services\LayananStokDarah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class PaginationHandler extends Handlers
{
    public static string|null $uri = '/';

    public static string|null $resource =
        KantongDarahResource::class;

    /**
     * Endpoint dapat diakses tanpa token Sanctum.
     */
    public static bool $public = true;

    /**
     * Ringkasan stok darah publik.
     *
     * Endpoint hanya menampilkan jumlah stok berdasarkan golongan darah
     * dan rhesus. Informasi kantong dan identitas pendonor tidak dikirim.
     */
    public function handler(
        Request $request,
        LayananStokDarah $layananStokDarah
    ): JsonResponse {
        return response()->json(
            $layananStokDarah->ringkasanPublik()
        );
    }
}