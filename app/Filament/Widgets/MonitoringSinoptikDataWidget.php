<?php

namespace App\Filament\Widgets;

use App\Jobs\FetchMonitoringSinoptikJob;
use App\Models\MonitoringSinoptik;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MonitoringSinoptikDataWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $today = Carbon::now()->format('Y-m-d');
        $nowHour = Carbon::now()->format('H');

        return $table
            ->query(
                MonitoringSinoptik::query()->where('periode', $today)
            )
            ->heading(' MATRIKS MONITORING METEOROLOGI SINOPTIK')
            ->description('Indikator ketersediaan data kiriman sinoptik per jam UTC untuk hari berjalan.')
            ->poll('30s')

            // PENYEGARAN VISUAL TABEL
            ->striped()
            // ->bordered()

            ->columns(
                array_merge(
                    array_map(function ($hour) use ($nowHour) {
                        $hourFormatted = sprintf('%02d:00', $hour);

                        return IconColumn::make("hourly_data.{$hourFormatted}")
                            ->label(sprintf('%02d', $hour)) // Menampilkan format 2 digit (00, 01, dst) di header kolom
                            ->boolean()
                            ->trueIcon('heroicon-s-check-circle') // Menggunakan ikon solid bulat yang lebih tebal
                            ->falseIcon($hour < $nowHour ? 'heroicon-s-x-circle' : 'heroicon-s-minus-circle')
                            ->trueColor('success')
                            ->falseColor($hour < $nowHour ? 'danger' : 'gray')
                            ->alignCenter();
                    }, range(0, 23)),

                    [
                        // Mengubah Teks Progress Biasa Menjadi Badge Indikator Kinerja yang Mencolok
                        TextColumn::make('progress')
                            ->label('Capaian (%)')
                            ->getStateUsing(fn ($record) => $record->progress)
                            ->suffix('%')
                            ->badge()
                            ->color(fn ($state): string => match (true) {
                                $state >= 100 => 'success', // 100% Hijau
                                $state >= 75  => 'info',    // >=75% Biru
                                $state >= 50  => 'warning', // >=50% Jingga
                                default       => 'danger',  // Dibawah itu Merah
                            })
                            ->alignCenter()
                    ]
                )
            )
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkronisasi Matriks')
                    ->size('sm')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        FetchMonitoringSinoptikJob::dispatchSync();
                        $this->render();
                        Notification::make()
                            ->title('Matriks sinoptik diperbarui dari bmkgsatu')
                            ->success()
                            ->send();
                    })
            ])
            ->paginated(false);
    }
}
