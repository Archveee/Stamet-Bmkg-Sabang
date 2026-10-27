<?php

namespace App\Filament\Resources\DataAwsResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DataAwsResource;
use App\Http\Requests\DataAwsRequest;

class CreateHandler extends Handlers
{
    public static string | null $uri = '/';
    public static string | null $resource = DataAwsResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel()
    {
        return static::$resource::getModel();
    }

    public function handler(DataAwsRequest $request)
    {
        $modelClass = static::getModel();

        // Cek apakah data dengan data_timestamp dan device_id sudah ada
        $existing = $modelClass::where('data_timestamp', $request->data_timestamp)
            ->where('device_id', $request->device_id)
            ->exists();

        if ($existing) {
            return response("Data dengan timestamp ini sudah ada", 409);
        }

        // Jika tidak ada duplikat, simpan data baru
        $model = new $modelClass();
        $model->fill($request->all());
        $model->save();

        return static::sendSuccessResponse($model, "Successfully Created Resource");
    }
}
