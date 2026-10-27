<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GtsMonitoringResource\Pages;
use App\Models\GtsMonitoring;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GtsMonitoringResource extends Resource
{
    protected static ?string $model = GtsMonitoring::class;
    protected static ?string $slug = 'gts-monitoring';
    protected static ?string $navigationLabel = 'GTS Monitoring';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')
                ->label('Tanggal Pengamatan (UTC)')
                ->required()
                ->displayFormat('d F Y')
                ->native(false)
                ->closeOnDateSelection()
                ->columnSpanFull(),

            Forms\Components\Section::make('SINOPTIK')
                ->columns(3)
                ->schema([
                    Forms\Components\TagsInput::make('sinoptik_missing_hours')
                        ->label('Jam Missing')
                        ->placeholder('Masukkan jam dan tekan Enter'),
                    Forms\Components\TagsInput::make('sinoptik_late_hours')
                        ->label('Jam Terlambat')
                        ->placeholder('Masukkan jam dan tekan Enter'),
                    Forms\Components\TagsInput::make('sinoptik_invalid_hours')
                        ->label('Jam Salah Sandi')
                        ->placeholder('Masukkan jam dan tekan Enter'),

                ]),

            Forms\Components\Section::make('METAR')
                ->columns(3)
                ->schema([
                    Forms\Components\TagsInput::make('metar_missing_hours')
                        ->label('Jam Missing')
                        ->placeholder('Masukkan jam dan tekan Enter'),
                    Forms\Components\TagsInput::make('metar_late_hours')
                        ->label('Jam Terlambat')
                        ->placeholder('Masukkan jam dan tekan Enter'),
                    Forms\Components\TagsInput::make('metar_invalid_hours')
                        ->label('Jam Salah Sandi')
                        ->placeholder('Masukkan jam dan tekan Enter'),

                ]),

            Forms\Components\Section::make('TAF')
                ->columns(3)
                ->schema([
                    Forms\Components\TagsInput::make('taf_missing_hours')
                        ->label('Jam Missing')
                        ->placeholder('Masukkan jam dan tekan Enter'),
                    Forms\Components\TagsInput::make('taf_late_hours')
                        ->label('Jam Terlambat')
                        ->placeholder('Masukkan jam dan tekan Enter'),
                    Forms\Components\TagsInput::make('taf_invalid_hours')
                        ->label('Jam Salah Sandi')
                        ->placeholder('Masukkan jam dan tekan Enter'),

                ]),
        ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\ColumnGroup::make('SINOPTIK', [
                    Tables\Columns\TextColumn::make('sinoptik_sent_percent')
                        ->label('Terkirim')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->formatStateUsing(fn($state) => $state . '%'),

                    Tables\Columns\TextColumn::make('sinoptik_ontime_percent')
                        ->label('Ontime')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->formatStateUsing(fn($state) => $state . '%'),

                    Tables\Columns\TextColumn::make('sinoptik_valid_percent')
                        ->label('Valid')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->placeholder('-')
                        ->formatStateUsing(fn($state) => $state . '%'),
                ])
                    ->alignCenter(),

                Tables\Columns\ColumnGroup::make('METAR', [
                    Tables\Columns\TextColumn::make('metar_sent_percent')
                        ->label('Terkirim')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->formatStateUsing(fn($state) => $state . '%'),

                    Tables\Columns\TextColumn::make('metar_ontime_percent')
                        ->label('Ontime')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->formatStateUsing(fn($state) => $state . '%'),

                    Tables\Columns\TextColumn::make('metar_valid_percent')
                        ->label('Valid')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->placeholder('-')
                        ->formatStateUsing(fn($state) => $state . '%'),
                ])
                    ->alignCenter(),

                Tables\Columns\ColumnGroup::make('TAF', [
                    Tables\Columns\TextColumn::make('taf_sent_percent')
                        ->label('Terkirim')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->formatStateUsing(fn($state) => $state . '%'),

                    Tables\Columns\TextColumn::make('taf_ontime_percent')
                        ->label('Ontime')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->formatStateUsing(fn($state) => $state . '%'),

                    Tables\Columns\TextColumn::make('taf_valid_percent')
                        ->label('Valid')
                        ->color(fn($state) => $state < 100 ? 'danger' : '')
                        ->placeholder('-')
                        ->formatStateUsing(fn($state) => $state . '%'),
                ])
                    ->alignCenter()

            ])
            ->defaultSort('date', 'desc')
            ->paginated([5, 10, 25, 50, 100])
            ->filters([
                Filter::make('date')
                    ->form([
                        Grid::make(2)
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
                            ->when($data['month'], fn($query, $month) => $query->whereMonth('date', $month))
                            ->when($data['year'], fn($query, $year) => $query->whereYear('date', $year));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGtsMonitorings::route('/'),
        ];
    }
}
