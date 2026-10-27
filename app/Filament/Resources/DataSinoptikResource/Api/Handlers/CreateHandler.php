<?php
namespace App\Filament\Resources\DataSinoptikResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DataSinoptikResource;
use App\Http\Requests\DataSinoptikRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = DataSinoptikResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    public function handler(DataSinoptikRequest $request)
    {
        $existingModel = static::getModel()::where(['data_timestamp' => $request->data_timestamp])->first();

        if ($existingModel) {
            // Jika data sudah ada, kembalikan respons 409
            return response()->json([
                'message' => 'Resource already exists',
                'data' => $existingModel,
            ], 409);
        }

        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}