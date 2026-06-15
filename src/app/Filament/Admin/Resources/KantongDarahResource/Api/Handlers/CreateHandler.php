<?php
namespace App\Filament\Admin\Resources\KantongDarahResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\KantongDarahResource;
use App\Filament\Admin\Resources\KantongDarahResource\Api\Requests\CreateKantongDarahRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = KantongDarahResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create KantongDarah
     *
     * @param CreateKantongDarahRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateKantongDarahRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}