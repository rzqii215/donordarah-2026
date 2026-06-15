<?php
namespace App\Filament\Admin\Resources\DistribusiDarahResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\DistribusiDarahResource;
use App\Filament\Admin\Resources\DistribusiDarahResource\Api\Requests\CreateDistribusiDarahRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = DistribusiDarahResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create DistribusiDarah
     *
     * @param CreateDistribusiDarahRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateDistribusiDarahRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}