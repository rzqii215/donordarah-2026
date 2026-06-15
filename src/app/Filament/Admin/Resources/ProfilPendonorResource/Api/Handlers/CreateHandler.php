<?php
namespace App\Filament\Admin\Resources\ProfilPendonorResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\ProfilPendonorResource;
use App\Filament\Admin\Resources\ProfilPendonorResource\Api\Requests\CreateProfilPendonorRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ProfilPendonorResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create ProfilPendonor
     *
     * @param CreateProfilPendonorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateProfilPendonorRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}