<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Admin\Resources\KantongDarahResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Admin\Resources\KantongDarahResource\Api\Transformers\KantongDarahTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = KantongDarahResource::class;


    /**
     * Show KantongDarah
     *
     * @param Request $request
     * @return KantongDarahTransformer
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');
        
        $query = static::getEloquentQuery();

        $query = QueryBuilder::for(
            $query->where(static::getKeyName(), $id)
        )
            ->first();

        if (!$query) return static::sendNotFoundResponse();

        return new KantongDarahTransformer($query);
    }
}
