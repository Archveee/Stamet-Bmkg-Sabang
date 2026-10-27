<?php
namespace App\Filament\Resources\PeralatanResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\PeralatanResource;
use App\Filament\Resources\PeralatanResource\Api\Requests\CreatePeralatanRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = PeralatanResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Peralatan
     *
     * @param CreatePeralatanRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreatePeralatanRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}