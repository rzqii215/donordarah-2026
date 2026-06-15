<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\PendaftaranDonorResource;
use App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Transformers\PendaftaranDonorTransformer;
use App\Support\Api\MemastikanPendonorTerautentikasi;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    use MemastikanPendonorTerautentikasi;

    public static string|null $uri = '/';

    public static string|null $resource =
        PendaftaranDonorResource::class;

    /**
     * Daftar pendaftaran milik Pendonor yang sedang login.
     */
    public function handler(
        Request $request
    ) {
        $pengguna = $this->penggunaPendonor(
            $request
        );

        $perPage = min(
            max(
                $request->integer('per_page', 10),
                1
            ),
            50
        );

        $query = QueryBuilder::for(
            static::getEloquentQuery()
                ->with('jadwal')
                ->where(
                    'pendonor_id',
                    $pengguna->id
                )
        )
            ->allowedFilters([
                AllowedFilter::exact('status'),

                AllowedFilter::exact(
                    'jadwal_donor_id'
                ),
            ])
            ->allowedSorts([
                'created_at',
                'updated_at',
                'status',
                'nomor_pendaftaran',
            ])
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends($request->query());

        return PendaftaranDonorTransformer::collection(
            $query
        );
    }
}