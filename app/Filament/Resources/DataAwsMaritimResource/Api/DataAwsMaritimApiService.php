<?php
namespace App\Filament\Resources\DataAwsMaritimResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\DataAwsMaritimResource;
use Illuminate\Routing\Router;


class DataAwsMaritimApiService extends ApiService
{
    protected static string | null $resource = DataAwsMaritimResource::class;

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
