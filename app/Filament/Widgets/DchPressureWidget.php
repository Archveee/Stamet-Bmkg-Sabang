<?php

namespace App\Filament\Widgets;

use App\Models\DataSinoptik;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DchPressureWidget extends BaseWidget
{
    use InteractsWithPageFilters;

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
                        MAX(CASE WHEN HOUR(data_timestamp) = 0 THEN pressure_qff_mb_derived END) AS jam_00,
                        MAX(CASE WHEN HOUR(data_timestamp) = 1 THEN pressure_qff_mb_derived END) AS jam_01,
                        MAX(CASE WHEN HOUR(data_timestamp) = 2 THEN pressure_qff_mb_derived END) AS jam_02,
                        MAX(CASE WHEN HOUR(data_timestamp) = 3 THEN pressure_qff_mb_derived END) AS jam_03,
                        MAX(CASE WHEN HOUR(data_timestamp) = 4 THEN pressure_qff_mb_derived END) AS jam_04,
                        MAX(CASE WHEN HOUR(data_timestamp) = 5 THEN pressure_qff_mb_derived END) AS jam_05,
                        MAX(CASE WHEN HOUR(data_timestamp) = 6 THEN pressure_qff_mb_derived END) AS jam_06,
                        MAX(CASE WHEN HOUR(data_timestamp) = 7 THEN pressure_qff_mb_derived END) AS jam_07,
                        MAX(CASE WHEN HOUR(data_timestamp) = 8 THEN pressure_qff_mb_derived END) AS jam_08,
                        MAX(CASE WHEN HOUR(data_timestamp) = 9 THEN pressure_qff_mb_derived END) AS jam_09,
                        MAX(CASE WHEN HOUR(data_timestamp) = 10 THEN pressure_qff_mb_derived END) AS jam_10,
                        MAX(CASE WHEN HOUR(data_timestamp) = 11 THEN pressure_qff_mb_derived END) AS jam_11,
                        MAX(CASE WHEN HOUR(data_timestamp) = 12 THEN pressure_qff_mb_derived END) AS jam_12,
                        MAX(CASE WHEN HOUR(data_timestamp) = 13 THEN pressure_qff_mb_derived END) AS jam_13,
                        MAX(CASE WHEN HOUR(data_timestamp) = 14 THEN pressure_qff_mb_derived END) AS jam_14,
                        MAX(CASE WHEN HOUR(data_timestamp) = 15 THEN pressure_qff_mb_derived END) AS jam_15,
                        MAX(CASE WHEN HOUR(data_timestamp) = 16 THEN pressure_qff_mb_derived END) AS jam_16,
                        MAX(CASE WHEN HOUR(data_timestamp) = 17 THEN pressure_qff_mb_derived END) AS jam_17,
                        MAX(CASE WHEN HOUR(data_timestamp) = 18 THEN pressure_qff_mb_derived END) AS jam_18,
                        MAX(CASE WHEN HOUR(data_timestamp) = 19 THEN pressure_qff_mb_derived END) AS jam_19,
                        MAX(CASE WHEN HOUR(data_timestamp) = 20 THEN pressure_qff_mb_derived END) AS jam_20,
                        MAX(CASE WHEN HOUR(data_timestamp) = 21 THEN pressure_qff_mb_derived END) AS jam_21,
                        MAX(CASE WHEN HOUR(data_timestamp) = 22 THEN pressure_qff_mb_derived END) AS jam_22,
                        MAX(CASE WHEN HOUR(data_timestamp) = 23 THEN pressure_qff_mb_derived END) AS jam_23,
                        ROUND(AVG(pressure_qff_mb_derived), 1) as rata_rata,
                        MAX(pressure_qff_mb_derived) as maksimum,
                        MIN(pressure_qff_mb_derived) as minimum
                    ")
                    ->groupByRaw('DATE(data_timestamp)')
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable(),

                ...array_map(function ($hour) {
                    $col = str_pad($hour, 2, '0', STR_PAD_LEFT);
                    return TextColumn::make("jam_$col")
                        ->label($col)
                        ->alignCenter()
                        ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 1, '.', '') : '-');
                }, range(0, 23)),

                // Tambahan kolom agregat
                TextColumn::make('rata_rata')
                    ->label('RATA2')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 1, '.', '') : '-'),

                TextColumn::make('maksimum')
                    ->label('MAX')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 1, '.', '') : '-'),

                TextColumn::make('minimum')
                    ->label('MIN')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 1, '.', '') : '-'),
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
