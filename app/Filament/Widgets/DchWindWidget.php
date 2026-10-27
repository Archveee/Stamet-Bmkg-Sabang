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

class DchWindWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    public function table(Table $table): Table
    {
        $hourColumns = collect(range(0, 23))->map(function ($hour) {
            $h = str_pad($hour, 2, '0', STR_PAD_LEFT);
            return "MAX(CASE WHEN HOUR(data_timestamp) = $hour THEN CONCAT(wind_dir_deg_dd, '/', wind_speed_ff) END) AS jam_$h";
        })->implode(",\n");

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
                        $hourColumns,
                        ROUND(AVG(wind_speed_ff), 0) as wind_speed_rata_rata,
                        MAX(wind_speed_ff) as wind_speed_maksimum,
                        MIN(wind_speed_ff) as wind_speed_minimum
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
                        ->alignCenter();
                }, range(0, 23)),

                TextColumn::make('wind_speed_rata_rata')
                    ->label('RATA2')
                    ->alignCenter(),

                TextColumn::make('wind_speed_maksimum')
                    ->label('MAX')
                    ->alignCenter(),

                TextColumn::make('wind_speed_minimum')
                    ->label('MIN')
                    ->alignCenter(),
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
