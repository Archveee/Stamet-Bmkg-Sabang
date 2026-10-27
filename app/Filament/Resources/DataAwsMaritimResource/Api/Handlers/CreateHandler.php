<?php
namespace App\Filament\Resources\DataAwsMaritimResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DataAwsMaritimResource;
use App\Filament\Resources\DataAwsMaritimResource\Api\Requests\CreateDataAwsMaritimRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = DataAwsMaritimResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create DataAwsMaritim
     *
     * @param CreateDataAwsMaritimRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateDataAwsMaritimRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}