<?php
namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\ProfilRumahSakitResource;

class DeleteHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = ProfilRumahSakitResource::class;

    public static function getMethod()
    {
        return Handlers::DELETE;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Delete ProfilRumahSakit
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->delete();

        return static::sendSuccessResponse($model, "Successfully Delete Resource");
    }
}