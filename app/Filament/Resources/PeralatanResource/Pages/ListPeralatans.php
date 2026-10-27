<?php

namespace App\Filament\Resources\PeralatanResource\Pages;

use App\Filament\Resources\PeralatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeralatans extends ListRecords
{
    protected static string $resource = PeralatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Tambah Peralatan')
            ->icon('heroicon-o-plus')
        ];
    }
}
