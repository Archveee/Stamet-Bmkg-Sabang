<?php

namespace App\Filament\Resources\DataAwsMaritimResource\Pages;

use App\Filament\Components\DataTabsFilter;
use App\Filament\Resources\DataAwsMaritimResource;
use App\Models\DataAwsMaritim;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataAwsMaritims extends ListRecords
{
    protected static string $resource = DataAwsMaritimResource::class;

    protected static ?string $title = 'Data AWS Maritim';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return DataTabsFilter::make(DataAwsMaritim::class, 'waktu');
    }
}
