<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\JadwalDonorResource;
use App\Filament\Admin\Resources\JadwalDonorResource\Api\Transformers\JadwalDonorTransformer;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    public static string|null $uri = '/';

    public static string|null $resource =
        JadwalDonorResource::class;

    /**
     * Endpoint dapat diakses tanpa token Sanctum.
     */
    public static bool $public = true;

    /**
     * Daftar jadwal donor yang sudah dipublikasikan.
     *
     * Filter yang tersedia:
     *
     * - filter[judul]
     * - filter[lokasi_donor_id]
     *
     * Sorting yang tersedia:
     *
     * - mulai_pada
     * - selesai_pada
     * - pendaftaran_dibuka_pada
     * - pendaftaran_ditutup_pada
     * - created_at
     *
     * Gunakan tanda minus untuk descending:
     *
     * - sort=-mulai_pada
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
                ->where('status', 'published')
        )
            ->allowedFilters([
                AllowedFilter::partial('judul'),

                AllowedFilter::exact(
                    'lokasi_donor_id'
                ),
            ])
            ->allowedSorts([
                'mulai_pada',
                'selesai_pada',
                'pendaftaran_dibuka_pada',
                'pendaftaran_ditutup_pada',
                'created_at',
            ])
            ->defaultSort('mulai_pada')
            ->paginate($perPage)
            ->appends($request->query());

        return JadwalDonorTransformer::collection(
            $query
        );
    }
}