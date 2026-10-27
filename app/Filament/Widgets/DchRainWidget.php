<?php

namespace App\Filament\Widgets;

use App\Models\DataSinoptik;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DchRainWidget extends BaseWidget
{

    protected static bool $isLazy = true;

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->emptyStateHeading('Tidak ada data')
            ->emptyStateIcon('heroicon-o-minus')
            ->searchOnBlur(true)
            ->deferLoading()
            ->recordUrl(null)
            ->defaultSort('tanggal', 'desc')
            ->query(
                DataSinoptik::query()
                    ->selectRaw("
                        MIN(id) as id,
                        DATE(DATE_SUB(data_timestamp, INTERVAL 1 DAY)) as tanggal,
                        MAX(IFNULL(rainfall_24h_rrrr, 0)) AS rainfall_24h_rrrr,
                        MAX(IFNULL(evaporation_24hours_mm_eee, 0)) AS evaporation_24hours_mm_eee,
                        MAX(IFNULL(sunshine_h_sss, 0)) AS sunshine_h_sss,
                        LEAST(ROUND(MAX(IFNULL(sunshine_h_sss, 0)) / 8 * 100), 100) AS sunshine_h_sss_percent
                     ")
                    ->groupByRaw('DATE(DATE_SUB(data_timestamp, INTERVAL 1 DAY))')
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable(),

                TextColumn::make('rainfall_24h_rrrr')
                    ->label('Hujan (mm)')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state ?? 0, 1)),

                TextColumn::make('evaporation_24hours_mm_eee')
                    ->label('Penguapan (mm)')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state ?? 0, 1)),

                TextColumn::make('sunshine_h_sss')
                    ->label('Penyinaran (jam)')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state ?? 0, 1)),

                TextColumn::make('sunshine_h_sss_percent')
                    ->label('Penyinaran (%)')
                    ->alignCenter()

            ])
            ->paginated([7, 10, 28, 29, 30, 31])
            ->filters([
                Filter::make('data_timestamp')
                    ->form([
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                Select::make('month')
                                    ->label('Bulan')
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        '1' => 'Januari',
                                        '2' => 'Februari',
                                        '3' => 'Maret',
                                        '4' => 'April',
                                        '5' => 'Mei',
                                        '6' => 'Juni',
                                        '7' => 'Juli',
                                        '8' => 'Agustus',
                                        '9' => 'September',
                                        '10' => 'Oktober',
                                        '11' => 'November',
                                        '12' => 'Desember',
                                    ])
                                    ->default(now()->month),

                                TextInput::make('year')
                                    ->label('Tahun')
                                    ->integer()
                                    ->default(now()->year),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'],
                                fn($query, $month) =>
                                $query->whereRaw('MONTH(DATE_SUB(data_timestamp, INTERVAL 1 DAY)) = ?', [$month])
                            )
                            ->when(
                                $data['year'],
                                fn($query, $year) =>
                                $query->whereRaw('YEAR(DATE_SUB(data_timestamp, INTERVAL 1 DAY)) = ?', [$year])
                            );
                    }),
            ]);
    }
}
