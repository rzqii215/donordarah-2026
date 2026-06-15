<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Api\Handlers;

use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Filament\Admin\Resources\DistribusiDarahResource\Api\Transformers\DistribusiDarahTransformer;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/';

    public static string|null $resource =
        DistribusiDarahResource::class;

    /**
     * Daftar distribusi darah milik Rumah Sakit yang sedang login.
     *
     * Filter:
     *
     * - filter[status]
     * - filter[permintaan_darah_id]
     *
     * Sorting:
     *
     * - nomor_distribusi
     * - dijadwalkan_pada
     * - diserahkan_pada
     * - created_at
     * - updated_at
     */
    public function handler(
        Request $request
    ) {
        $profilRumahSakit = $this->profilRumahSakit(
            request: $request,
            harusTerverifikasi: true,
        );

        $perPage = min(
            max(
                $request->integer(
                    'per_page',
                    10
                ),
                1
            ),
            50
        );

        $queryDasar = static::getEloquentQuery()
            ->with([
                'permintaan.rumahSakit',
            ])
            ->whereHas(
                'permintaan',
                function (
                    Builder $query
                ) use (
                    $profilRumahSakit
                ): void {
                    $query->where(
                        'profil_rumah_sakit_id',
                        $profilRumahSakit->id
                    );
                }
            );

        $query = QueryBuilder::for(
            $queryDasar
        )
            ->allowedFilters([
                AllowedFilter::exact(
                    'status'
                ),

                AllowedFilter::exact(
                    'permintaan_darah_id'
                ),
            ])
            ->allowedSorts([
                'nomor_distribusi',
                'dijadwalkan_pada',
                'diserahkan_pada',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends($request->query());

        return DistribusiDarahTransformer::collection(
            $query
        );
    }
}