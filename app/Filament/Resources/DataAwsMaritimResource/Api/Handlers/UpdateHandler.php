<?php
namespace App\Filament\Resources\DataAwsMaritimResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DataAwsMaritimResource;
use App\Filament\Resources\DataAwsMaritimResource\Api\Requests\UpdateDataAwsMaritimRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = DataAwsMaritimResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update DataAwsMaritim
     *
     * @param UpdateDataAwsMaritimRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateDataAwsMaritimRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}