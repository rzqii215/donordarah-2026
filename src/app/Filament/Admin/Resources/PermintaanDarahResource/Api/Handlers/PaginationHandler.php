<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Transformers\PermintaanDarahTransformer;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/';

    public static string|null $resource =
        PermintaanDarahResource::class;

    /**
     * Daftar pengajuan kebutuhan donor milik Pemohon Donor yang sedang login.
     */
    public function handler(
        Request $request
    ) {
        $profil = $this->profilRumahSakit(
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

        $query = QueryBuilder::for(
            static::getEloquentQuery()
                ->with([
                    'rumahSakit',
                    'peninjau',
                ])
                ->where(
                    'profil_rumah_sakit_id',
                    $profil->id
                )
        )
            ->allowedFilters([
                AllowedFilter::exact('status'),

                AllowedFilter::exact(
                    'tingkat_urgensi'
                ),

                AllowedFilter::exact(
                    'golongan_darah'
                ),

                AllowedFilter::exact('rhesus'),
            ])
            ->allowedSorts([
                'created_at',
                'updated_at',
                'dibutuhkan_pada',
                'status',
                'tingkat_urgensi',
                'nomor_permintaan',
            ])
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends($request->query());

        return PermintaanDarahTransformer::collection(
            $query
        );
    }
}