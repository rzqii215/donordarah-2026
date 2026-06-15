<?php
namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\ProfilRumahSakitResource;
use App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Requests\CreateProfilRumahSakitRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ProfilRumahSakitResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create ProfilRumahSakit
     *
     * @param CreateProfilRumahSakitRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateProfilRumahSakitRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}