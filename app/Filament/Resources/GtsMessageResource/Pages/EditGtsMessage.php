<?php

namespace App\Filament\Resources\GtsMessageResource\Pages;

use App\Filament\Resources\GtsMessageResource;
use App\Helpers\GtsMessageHelper;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGtsMessage extends EditRecord
{
    protected static string $resource = GtsMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
