<?php

namespace App\Filament\Resources\DataSinoptikResource\Pages;

use App\Filament\Resources\DataSinoptikResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDataSinoptik extends CreateRecord
{
    protected static string $resource = DataSinoptikResource::class;

    protected static bool $canCreateAnother = false;

}
