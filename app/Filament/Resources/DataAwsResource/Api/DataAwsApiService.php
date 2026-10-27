<?php
namespace App\Filament\Resources\DataAwsResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\DataAwsResource;
use Illuminate\Routing\Router;


class DataAwsApiService extends ApiService
{
    protected static string | null $resource = DataAwsResource::class;

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
