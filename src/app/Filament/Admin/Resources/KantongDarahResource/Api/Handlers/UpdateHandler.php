<?php
namespace App\Filament\Admin\Resources\KantongDarahResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\KantongDarahResource;
use App\Filament\Admin\Resources\KantongDarahResource\Api\Requests\UpdateKantongDarahRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = KantongDarahResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update KantongDarah
     *
     * @param UpdateKantongDarahRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateKantongDarahRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}