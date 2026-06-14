<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\LokasiDonorResource;
use App\Filament\Admin\Resources\LokasiDonorResource\Api\Transformers\LokasiDonorTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    public static string|null $uri = '/';

    public static string|null $resource =
        LokasiDonorResource::class;

    /**
     * Endpoint dapat diakses tanpa token Sanctum.
     */
    public static bool $public = true;

    /**
     * Daftar lokasi donor.
     *
     * Filter:
     *
     * - filter[q]
     * - filter[kota]
     * - filter[provinsi]
     *
     * Sorting:
     *
     * - nama_lokasi
     * - kota
     * - provinsi
     * - created_at
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function handler(Request $request)
    {
        $perPage = min(
            max(
                $request->integer('per_page', 12),
                1
            ),
            50
        );

        $query = QueryBuilder::for(
            static::getEloquentQuery()
        )
            ->allowedFilters([
                AllowedFilter::callback(
                    'q',
                    function (
                        Builder $query,
                        mixed $value
                    ): void {
                        $pencarian = trim(
                            (string) $value
                        );

                        if ($pencarian === '') {
                            return;
                        }

                        $query->where(
                            function (
                                Builder $subQuery
                            ) use (
                                $pencarian
                            ): void {
                                $subQuery
                                    ->where(
                                        'nama_lokasi',
                                        'like',
                                        '%' . $pencarian . '%'
                                    )
                                    ->orWhere(
                                        'alamat',
                                        'like',
                                        '%' . $pencarian . '%'
                                    )
                                    ->orWhere(
                                        'kota',
                                        'like',
                                        '%' . $pencarian . '%'
                                    );
                            }
                        );
                    }
                ),

                AllowedFilter::partial('kota'),

                AllowedFilter::partial('provinsi'),
            ])
            ->allowedSorts([
                'nama_lokasi',
                'kota',
                'provinsi',
                'created_at',
            ])
            ->defaultSort('nama_lokasi')
            ->paginate($perPage)
            ->appends($request->query());

        return LokasiDonorTransformer::collection(
            $query
        );
    }
}