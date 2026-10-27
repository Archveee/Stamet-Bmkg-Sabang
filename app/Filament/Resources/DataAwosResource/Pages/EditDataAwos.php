<?php

namespace App\Filament\Resources\DataAwosResource\Pages;

use App\Filament\Resources\DataAwosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataAwos extends EditRecord
{
    protected static string $resource = DataAwosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
