<?php

namespace App\Filament\Resources\DataAwsResource\Pages;

use App\Filament\Resources\DataAwsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataAws extends EditRecord
{
    protected static string $resource = DataAwsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
