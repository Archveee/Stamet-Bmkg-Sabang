<?php

namespace App\Filament\Resources\DataAwosResource\Pages;

use App\Filament\Components\DataTabsFilter;
use App\Filament\Resources\DataAwosResource;
use App\Models\DataAwos;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;


class ListDataAwos extends ListRecords
{
    protected static string $resource = DataAwosResource::class;

    protected static ?string $title = 'Data AWOS';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return DataTabsFilter::make(DataAwos::class);
    }
}
