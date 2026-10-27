<?php

namespace App\Filament\Resources\DataSinoptikResource\Pages;

use App\Filament\Resources\DataSinoptikResource;
use App\Helpers\DateHelper;
use App\Jobs\FetchSinoptikDataJob;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class EditDataSinoptik extends EditRecord
{
    protected static string $resource = DataSinoptikResource::class;

    public function getSubHeading(): string|Htmlable
    {
        $utcTimestamp = Carbon::parse($this->record->data_timestamp);
        $utcHour = $utcTimestamp->format('H');

        $utcDiff = setting('station_utc_diff', 7);
        $localHour = $utcTimestamp->copy()->addHours($utcDiff)->format('H');

        $zone = match ((int) $utcDiff) {
            7 => 'WIB',
            8 => 'WITA',
            9 => 'WIT',
            default => '',
        };

        return "Jam Pengamatan {$utcHour} UTC ({$localHour} {$zone})";
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label('Sync')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-arrow-path')
                ->modalHeading('Sinkronisasi Data Sinoptik')
                ->modalDescription('Sinkronkan ulang dengan data terbaru dari excel?')
                ->modalSubmitActionLabel('Ya')
                ->action(function () {
                    $formattedTimestamp = DateHelper::formatDataTimestamp($this->record->data_timestamp);
                    FetchSinoptikDataJob::dispatchSync($formattedTimestamp, auth()->id());
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
