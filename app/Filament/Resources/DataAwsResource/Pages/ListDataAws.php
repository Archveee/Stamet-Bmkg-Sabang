<?php

namespace App\Filament\Resources\DataAwsResource\Pages;

use App\Filament\Components\DataTabsFilter;
use App\Filament\Resources\DataAwsResource;
use App\Helpers\AwsDataHelper;
use App\Helpers\DateHelper;
use App\Jobs\FillExcelSinoptikServerJob;
use App\Jobs\SendDataAwsToServerSinoptikJob;
use App\Models\DataAws;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;

class ListDataAws extends ListRecords
{
    protected static string $resource = DataAwsResource::class;

    protected static ?string $title = 'Data AWS';

    protected function getHeaderActions(): array
    {
        return [
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
                Actions\Action::make('restartAws')
                    ->label('Restart Server AWS')
                    ->icon('heroicon-o-arrow-path')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->modalWidth('2xl')
                    ->modalHeading('Restart Server AWS')
                    ->modalDescription('Server AWS akan restart dan butuh waktu beberapa menit untuk normal kembali. Tindakan ini tidak disarankan.')
                    ->modalSubmitActionLabel('Restart')
                    ->form([
                        TextInput::make('confirmation_text')
                            ->label('Konfirmasi')
                            ->required()
                            ->placeholder('Ketik CONFIRM')
                            ->rules([
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    if ($value != 'CONFIRM') {
                                        $fail('Teks konfirmasi salah!');
                                    }
                                },
                            ])
                    ])
                    ->action(function (): void {
                        $result = AwsDataHelper::restartAws();

                        if ($result['status'] === 'success') {
                            Notification::make()
                                ->title('Berhasil merestart server AWS')
                                ->body('Silahkan tunggu 2 atau 3 menit sampai AWS normal kembali')
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->duration(3000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal merestart server AWS')
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
        return DataTabsFilter::make(DataAws::class);
    }
}
