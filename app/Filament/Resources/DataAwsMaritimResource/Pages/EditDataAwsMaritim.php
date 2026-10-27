<?php

namespace App\Filament\Resources\DataAwsMaritimResource\Pages;

use App\Filament\Resources\DataAwsMaritimResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataAwsMaritim extends EditRecord
{
    protected static string $resource = DataAwsMaritimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
