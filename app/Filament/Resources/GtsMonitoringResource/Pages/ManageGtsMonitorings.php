<?php

namespace App\Filament\Resources\GtsMonitoringResource\Pages;

use App\Filament\Resources\GtsMonitoringResource;
use App\Filament\Widgets\GtsDailyMonitoringTable;
use App\Jobs\FetchGtsMonitoringDataJob;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Carbon;

class ManageGtsMonitorings extends ManageRecords
{
    protected static string $resource = GtsMonitoringResource::class;

    protected static ?string $title = 'GTS Monitoring';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('fetchData')
                ->label('Tarik Data')
                ->icon('heroicon-o-inbox-arrow-down')
                ->modalWidth('2xl')
                ->modalHeading('Tarik Data Monitoring GTS Dari BMKGSatu')
                ->modalSubmitActionLabel('Proses')
                ->form([
                    Select::make('type_message')
                        ->label('Jenis Pesan GTS')
                        ->helperText('Biarkan kosong untuk download semua jenis')
                        ->placeholder('Pilih salah satu')
                        ->options(function () {
                            return setting('bmkgsatu_type_message');
                        }),

                    Grid::make()
                        ->columns(2)
                        ->schema([
                            DatePicker::make('date_timestamp_from')
                                ->label('Dari Tanggal')
                                ->native(false)
                                ->displayFormat('Y-m-d')
                                ->closeOnDateSelection()
                                ->default(now())
                                ->maxDate(now()),

                            DatePicker::make('date_timestamp_until')
                                ->label('s/d Tanggal')
                                ->native(false)
                                ->displayFormat('Y-m-d')
                                ->closeOnDateSelection()
                                ->default(now())
                                ->maxDate(now())

                        ])

                ])
                ->action(function (array $data): void {
                    $typeMessage = (int) $data['type_message'];

                    $startDate = Carbon::parse($data['date_timestamp_from'])->format('Y-m-d\T00:00:00');
                    $endDate = Carbon::parse($data['date_timestamp_until'])->format('Y-m-d\T23:59:59');

                    if (!$typeMessage) {
                        // Download sinoptik, metar, and taf
                        FetchGtsMonitoringDataJob::dispatchSync(1, $startDate, $endDate);
                        FetchGtsMonitoringDataJob::dispatchSync(4, $startDate, $endDate);
                        FetchGtsMonitoringDataJob::dispatchSync(6, $startDate, $endDate);
                    } else {
                        FetchGtsMonitoringDataJob::dispatchSync($typeMessage, $startDate, $endDate);
                    }

                    Notification::make()
                        ->title('Data monitoring GTS berhasil ditarik')
                        ->success()
                        ->duration(3000)
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GtsDailyMonitoringTable::class
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
