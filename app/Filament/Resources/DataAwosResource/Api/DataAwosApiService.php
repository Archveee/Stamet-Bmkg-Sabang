<?php
namespace App\Filament\Resources\DataAwosResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\DataAwosResource;
use Illuminate\Routing\Router;


class DataAwosApiService extends ApiService
{
    protected static string | null $resource = DataAwosResource::class;

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
