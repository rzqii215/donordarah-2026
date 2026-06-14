<?php
namespace App\Filament\Admin\Resources\LokasiDonorResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\LokasiDonorResource;
use App\Filament\Admin\Resources\LokasiDonorResource\Api\Requests\CreateLokasiDonorRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = LokasiDonorResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create LokasiDonor
     *
     * @param CreateLokasiDonorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateLokasiDonorRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}