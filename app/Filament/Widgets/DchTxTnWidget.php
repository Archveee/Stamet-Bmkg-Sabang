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

class DchTxTnWidget extends BaseWidget
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
                        DATE(data_timestamp) as tanggal,
                        MAX(temp_max_c_txtxtx) as suhu_maksimum,
                        (
                            SELECT MIN(ds2.temp_min_c_tntntn)
                            FROM data_sinoptik ds2
                            WHERE DATE(ds2.data_timestamp) = DATE(MIN(data_sinoptik.data_timestamp) + INTERVAL 1 DAY)
                        ) as suhu_minimum
                    ")
                    ->groupByRaw('DATE(data_timestamp)')
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable(),

                TextColumn::make('suhu_maksimum')
                    ->label('Suhu Maksimum')
                    ->alignCenter(),

                TextColumn::make('suhu_minimum')
                    ->label('Suhu Minimum')
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
                            ->when($data['month'], fn($query, $month) => $query->whereMonth('data_timestamp', $month))
                            ->when($data['year'], fn($query, $year) => $query->whereYear('data_timestamp', $year));
                    }),
            ]);
    }
}