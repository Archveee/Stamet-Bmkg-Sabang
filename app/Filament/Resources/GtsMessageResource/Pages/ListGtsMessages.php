<?php

namespace App\Filament\Resources\GtsMessageResource\Pages;

use App\Filament\Components\InputObserverAction;
use App\Filament\Components\SentAndCreationModeSettingFormSchema;
use App\Filament\Resources\GtsMessageResource;
use App\Filament\Widgets\MonitoringGtsMessages;
use App\Filament\Widgets\PendingGtsMessages;
use App\Jobs\FetchGtsMessagesJob;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListGtsMessages extends ListRecords
{
    protected static string $resource = GtsMessageResource::class;

    protected static ?string $title = 'GTS Messages';

    protected function getHeaderActions(): array
    {
        return [
            InputObserverAction::make(),
            Actions\CreateAction::make()
                ->label('Buat Pesan')
                ->color('info')
                ->icon('heroicon-o-paper-airplane'),
            Actions\Action::make('fetchData')
                ->label('Tarik Data')
                ->icon('heroicon-o-inbox-arrow-down')
                ->modalWidth('2xl')
                ->modalHeading('Tarik Data Monitoring GTS Dari BMKGSatu')
                ->modalSubmitActionLabel('Proses')
                ->form([
                    Select::make('type_message')
                        ->label('Jenis Pesan GTS')
                        ->required()
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
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($set, ?string $state) {
                                    $fromDate = Carbon::parse($state);
                                    $untilDate = $fromDate->copy();
                                    $set('date_timestamp_until', $untilDate->format('Y-m-d'));
                                }),

                            DatePicker::make('date_timestamp_until')
                                ->label('s/d Tanggal')
                                ->native(false)
                                ->displayFormat('Y-m-d')
                                ->closeOnDateSelection()
                                ->default(now())

                        ])

                ])
                ->action(function (array $data): void {
                    $typeMessage = (int) $data['type_message'];

                    $startDate = Carbon::parse($data['date_timestamp_from'])->format('Y-m-d\T00:00:00');
                    $endDate = Carbon::parse($data['date_timestamp_until'])->format('Y-m-d\T23:59:59');

                    FetchGtsMessagesJob::dispatchSync($typeMessage, $startDate, $endDate);
                    Notification::make()
                        ->title('Data GTS berhasil ditarik')
                        ->success()
                        ->duration(3000)
                        ->send();
                }),
            Actions\Action::make('setSentAndCreationMode')
                ->label('Setting')
                ->modalHeading('Setting Mode Pesan')
                ->modalDescription('Semua offset +/- terhitung dari jam pengiriman.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->slideOver()
                ->modalWidth('md')
                ->modalSubmitActionLabel('Simpan')
                ->form(SentAndCreationModeSettingFormSchema::make())
                ->fillForm(function () {
                    $keys = [
                        'bmkgsatu_metar_sent_mode',
                        'bmkgsatu_metar_creation_mode',
                        'bmkgsatu_metar_sent_mode_offset',
                        'bmkgsatu_metar_creation_mode_offset',

                        'bmkgsatu_sinoptik_sent_mode',
                        'bmkgsatu_sinoptik_creation_mode',
                        'bmkgsatu_sinoptik_sent_mode_offset',
                        'bmkgsatu_sinoptik_creation_mode_offset',

                        'bmkgsatu_wxrev_sent_mode',
                        'bmkgsatu_wxrev_creation_mode',
                        'bmkgsatu_wxrev_sent_mode_offset',
                        'bmkgsatu_wxrev_creation_mode_offset',

                        'bmkgsatu_dch_sent_mode',
                        'bmkgsatu_dch_creation_mode',
                        'bmkgsatu_dch_sent_mode_offset',
                        'bmkgsatu_dch_creation_mode_offset',

                        'bmkgsatu_taf_sent_mode',
                        'bmkgsatu_taf_creation_mode',
                        'bmkgsatu_taf_sent_mode_offset',
                        'bmkgsatu_taf_creation_mode_offset',
                    ];

                    return collect(setting($keys))->toArray();
                })
                ->action(function (array $data): void {
                    foreach ($data as $name => $value) {
                        Setting::where('setting_name', $name)->update([
                            'setting_value' => $value,
                        ]);

                        cache()->forget('settings');
                    }

                    Notification::make()
                        ->title('Mode Pengiriman Berhasil Diupdate')
                        ->success()
                        ->duration(2000)
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MonitoringGtsMessages::class,
            PendingGtsMessages::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getTabs(): array
    {

        return [
            'all' => Tab::make('SEMUA'),
            'sinoptik' => Tab::make('SINOPTIK')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type_message', '1')),
            'metar' => Tab::make('METAR')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type_message', '4')),
            'wxrev' => Tab::make('WXREV')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type_message', '8')),
            'dch' => Tab::make('DATA CUACA HARIAN')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type_message', '53')),
            'taf' => Tab::make('TAF')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type_message', '6')),

        ];
    }
}
