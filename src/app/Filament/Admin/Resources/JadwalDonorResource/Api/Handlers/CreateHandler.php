<?php
namespace App\Filament\Admin\Resources\JadwalDonorResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\JadwalDonorResource;
use App\Filament\Admin\Resources\JadwalDonorResource\Api\Requests\CreateJadwalDonorRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = JadwalDonorResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create JadwalDonor
     *
     * @param CreateJadwalDonorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateJadwalDonorRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}