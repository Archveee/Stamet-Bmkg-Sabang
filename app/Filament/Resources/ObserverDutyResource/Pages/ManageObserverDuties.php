<?php

namespace App\Filament\Resources\ObserverDutyResource\Pages;

use App\Filament\Resources\ObserverDutyResource;
use Filament\Resources\Pages\ManageRecords;

class ManageObserverDuties extends ManageRecords
{
    protected static string $resource = ObserverDutyResource::class;

    protected static ?string $title = 'Observer on Duty';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->createAnother(false)
        ];
    }
}
