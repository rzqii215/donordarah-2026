<?php
namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Transformers\ProfilRumahSakitTransformer;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ProfilRumahSakitResource::class;


    /**
     * List of ProfilRumahSakit
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function handler()
    {
        $query = static::getEloquentQuery();

        $query = QueryBuilder::for($query)
        ->allowedFields($this->getAllowedFields() ?? [])
        ->allowedSorts($this->getAllowedSorts() ?? [])
        ->allowedFilters($this->getAllowedFilters() ?? [])
        ->allowedIncludes($this->getAllowedIncludes() ?? [])
        ->paginate(request()->query('per_page'))
        ->appends(request()->query());

        return ProfilRumahSakitTransformer::collection($query);
    }
}
