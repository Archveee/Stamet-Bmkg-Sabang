<?php

namespace App\Filament\Resources\DataSinoptikResource\Pages;

use App\Filament\Components\InputObserverAction;
use App\Filament\Resources\DataSinoptikResource;
use App\Filament\Widgets\MonitoringSinoptikDataWidget;
use App\Helpers\DateHelper;
use App\Helpers\SinoptikDataHelper;
use App\Jobs\FetchSinoptikDataJob;
use App\Jobs\SendDataAwsToServerSinoptikJob;
use App\Models\DataSinoptik;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListDataSinoptiks extends ListRecords
{
    protected static string $resource = DataSinoptikResource::class;

    protected $listeners = [
        'update-sinoptik-table' => 'updateSinoptikTable'
    ];

    public function updateSinoptikTable()
    {
        $this->resetPage();
    }

    protected function getHeaderActions(): array
    {
        return [
            InputObserverAction::make(),
            Actions\Action::make('fetchData')
                ->label('Tarik Data')
                ->icon('heroicon-o-inbox-arrow-down')
                ->modalWidth('2xl')
                ->modalHeading('Tarik Data Dari Server Sinoptik')
                ->modalDescription('Pastikan kedua server saling terhubung')
                ->modalSubmitActionLabel('Proses')
                ->form([
                    Section::make('Jam Pengamatan (UTC)')
                        ->columns(2)
                        ->schema([
                            DateTimePicker::make('timestamp_from')
                                ->label('Dari jam')
                                ->native(false)
                                ->seconds(false)
                                ->displayFormat('Y-m-d H:i')
                                ->default(DateHelper::getNearestSentHour())
                                ->live(onBlur: true)
                                ->closeOnDateSelection()
                                ->afterStateUpdated(function ($set, ?string $state) {
                                    $fromDatetime = Carbon::parse($state);
                                    $untilDatetime = $fromDatetime->copy();
                                    if ($fromDatetime->isSameDay(now())) {
                                        $set('timestamp_until', $untilDatetime->format('Y-m-d H:i'));
                                    } else {
                                        $set('timestamp_until', $untilDatetime->endOfDay()->format('Y-m-d H:i'));
                                    }
                                }),
                            DateTimePicker::make('timestamp_until')
                                ->label('s/d jam')
                                ->native(false)
                                ->seconds(false)
                                ->displayFormat('Y-m-d H:i')
                                ->default(DateHelper::getNearestSentHour())
                                ->closeOnDateSelection()
                                ->maxDate(
                                    fn($get): string =>
                                    Carbon::parse($get('timestamp_from'))->addMonth()->format('Y-m-d H:i') # max 1 month
                                )
                        ])

                ])
                ->action(function (array $data): void {
                    $timestampFrom = DateHelper::formatDataTimestamp($data['timestamp_from']);
                    $timestampUntil = DateHelper::formatDataTimestamp($data['timestamp_until']);
                    // range
                    if ($timestampUntil > $timestampFrom) {
                        $timestamp = $timestampFrom . '-' . $timestampUntil;
                    } else {
                        $timestamp = $timestampFrom;
                    }
                    Notification::make()
                        ->title('Mengambil data dari server sinoptik...')
                        ->body('Harap tunggu sampai notifikasi berikutnya')
                        ->icon('heroicon-o-information-circle')
                        ->success()
                        ->duration(10000)
                        ->send();

                    FetchSinoptikDataJob::dispatch($timestamp, auth()->id());
                }),

            Actions\ActionGroup::make([
                Actions\Action::make('sendData')
                    ->label('Isi Excel Sinoptik')
                    ->icon('heroicon-o-bars-arrow-up')
                    ->color('warning')
                    ->modalWidth('2xl')
                    ->modalHeading('Isi Excel Sinoptik')
                    ->modalDescription('Excel akan diisi menggunakan data AWS menit ke-50')
                    ->modalSubmitActionLabel('Kirim')
                    ->form([
                        DateTimePicker::make('timestamp')
                            ->label('Jam Pengamatan')
                            ->native(false)
                            ->seconds(false)
                            ->suffix('UTC')
                            ->displayFormat('Y-m-d H:i')
                            ->default(DateHelper::getNearestSentHour()),
                        TextInput::make('minute')
                            ->label('Pakai data AWS menit ke')
                            ->integer()
                            ->default(50)
                            ->maxValue(59)

                    ])
                    ->action(function (array $data): void {
                        // timestamp "YYYY-MM-DD HH:ii:SS"
                        $timestamp = Carbon::parse($data['timestamp'])->toDateTimeString();
                        $minute = $data['minute'];
                        // range
                        Notification::make()
                            ->title('Mengisi Excel Menggunakan Data AWS')
                            ->body('Harap periksa excel sinoptik apakah pengisian berhasil')
                            ->icon('heroicon-o-information-circle')
                            ->success()
                            ->duration(5000)
                            ->send();

                        SendDataAwsToServerSinoptikJob::dispatch($timestamp, $minute, auth()->id());
                    }),
                Actions\Action::make('restartExcel')
                    ->label('Restart Excel Sinoptik')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalWidth('2xl')
                    ->modalHeading('Restart Program Excel Sinoptik')
                    ->modalDescription('Semua jendela excel yang sedang terbuka akan ditutup paksa dan data yang tidak tersimpan akan hilang. Apakah Anda Yakin?')
                    ->modalSubmitActionLabel('Ya, Lanjutkan')
                    ->action(function (): void {
                        $result = SinoptikDataHelper::restartExcelSinoptik();

                        if ($result['status'] === 'success') {
                            Notification::make()
                                ->title('Berhasil merestart excel sinoptik')
                                ->body($result['detail'] ?? '')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->duration(3000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal merestart excel sinoptik')
                                ->body($result['detail'] ?? '')
                                ->icon('heroicon-o-x-circle')
                                ->danger()
                                ->duration(5000)
                                ->send();
                        }
                    }),
            ])->dropdownOffset(20)

        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),

            'today' => Tab::make('Hari Ini')
                ->badge(fn() => DataSinoptik::whereDate('data_timestamp', Carbon::today())->count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('data_timestamp', Carbon::today())
                ),

            'yesterday' => Tab::make('Kemarin')
                ->badge(fn() => DataSinoptik::whereDate('data_timestamp', Carbon::yesterday())->count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('data_timestamp', Carbon::yesterday())
                ),

            'last_7_days' => Tab::make('7 Hari Terakhir')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereBetween('data_timestamp', [
                        Carbon::now()->subDays(7),
                        Carbon::now(),
                    ])
                ),

            'last_30_days' => Tab::make('30 Hari Terakhir')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereBetween('data_timestamp', [
                        Carbon::now()->subDays(30),
                        Carbon::now(),
                    ])
                ),

            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereMonth('data_timestamp', Carbon::now()->month)
                        ->whereYear('data_timestamp', Carbon::now()->year)
                ),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MonitoringSinoptikDataWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
