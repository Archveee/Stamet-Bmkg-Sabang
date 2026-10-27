<?php
namespace App\Filament\Resources\DataSinoptikResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\DataSinoptikResource;
use Illuminate\Routing\Router;


class DataSinoptikApiService extends ApiService
{
    protected static string | null $resource = DataSinoptikResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
