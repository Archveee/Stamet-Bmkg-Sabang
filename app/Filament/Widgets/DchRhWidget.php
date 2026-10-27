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

class DchRhWidget extends BaseWidget
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
                        MAX(CASE WHEN HOUR(data_timestamp) = 0 THEN relative_humidity_pc END) AS jam_00,
                        MAX(CASE WHEN HOUR(data_timestamp) = 1 THEN relative_humidity_pc END) AS jam_01,
                        MAX(CASE WHEN HOUR(data_timestamp) = 2 THEN relative_humidity_pc END) AS jam_02,
                        MAX(CASE WHEN HOUR(data_timestamp) = 3 THEN relative_humidity_pc END) AS jam_03,
                        MAX(CASE WHEN HOUR(data_timestamp) = 4 THEN relative_humidity_pc END) AS jam_04,
                        MAX(CASE WHEN HOUR(data_timestamp) = 5 THEN relative_humidity_pc END) AS jam_05,
                        MAX(CASE WHEN HOUR(data_timestamp) = 6 THEN relative_humidity_pc END) AS jam_06,
                        MAX(CASE WHEN HOUR(data_timestamp) = 7 THEN relative_humidity_pc END) AS jam_07,
                        MAX(CASE WHEN HOUR(data_timestamp) = 8 THEN relative_humidity_pc END) AS jam_08,
                        MAX(CASE WHEN HOUR(data_timestamp) = 9 THEN relative_humidity_pc END) AS jam_09,
                        MAX(CASE WHEN HOUR(data_timestamp) = 10 THEN relative_humidity_pc END) AS jam_10,
                        MAX(CASE WHEN HOUR(data_timestamp) = 11 THEN relative_humidity_pc END) AS jam_11,
                        MAX(CASE WHEN HOUR(data_timestamp) = 12 THEN relative_humidity_pc END) AS jam_12,
                        MAX(CASE WHEN HOUR(data_timestamp) = 13 THEN relative_humidity_pc END) AS jam_13,
                        MAX(CASE WHEN HOUR(data_timestamp) = 14 THEN relative_humidity_pc END) AS jam_14,
                        MAX(CASE WHEN HOUR(data_timestamp) = 15 THEN relative_humidity_pc END) AS jam_15,
                        MAX(CASE WHEN HOUR(data_timestamp) = 16 THEN relative_humidity_pc END) AS jam_16,
                        MAX(CASE WHEN HOUR(data_timestamp) = 17 THEN relative_humidity_pc END) AS jam_17,
                        MAX(CASE WHEN HOUR(data_timestamp) = 18 THEN relative_humidity_pc END) AS jam_18,
                        MAX(CASE WHEN HOUR(data_timestamp) = 19 THEN relative_humidity_pc END) AS jam_19,
                        MAX(CASE WHEN HOUR(data_timestamp) = 20 THEN relative_humidity_pc END) AS jam_20,
                        MAX(CASE WHEN HOUR(data_timestamp) = 21 THEN relative_humidity_pc END) AS jam_21,
                        MAX(CASE WHEN HOUR(data_timestamp) = 22 THEN relative_humidity_pc END) AS jam_22,
                        MAX(CASE WHEN HOUR(data_timestamp) = 23 THEN relative_humidity_pc END) AS jam_23,
                        ROUND(AVG(relative_humidity_pc)) as rata_rata,
                        MAX(relative_humidity_pc) as maksimum,
                        MIN(relative_humidity_pc) as minimum
                    ")
                    ->groupByRaw('DATE(data_timestamp)'),
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable(),

                ...array_map(function ($hour) {
                    $col = str_pad($hour, 2, '0', STR_PAD_LEFT);
                    return TextColumn::make("jam_$col")
                        ->label($col)
                        ->alignCenter();
                }, range(0, 23)),

                // Tambahan kolom agregat
                TextColumn::make('rata_rata')
                    ->label('RATA2')
                    ->alignCenter(),

                TextColumn::make('maksimum')
                    ->label('MAX')
                    ->alignCenter(),

                TextColumn::make('minimum')
                    ->label('MIN')
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
