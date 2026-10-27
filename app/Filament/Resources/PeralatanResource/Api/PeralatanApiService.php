<?php
namespace App\Filament\Resources\PeralatanResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\PeralatanResource;
use Illuminate\Routing\Router;


class PeralatanApiService extends ApiService
{
    protected static string | null $resource = PeralatanResource::class;

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
