<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataAwsResource\Pages;
use App\Models\DataAws;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DataAwsResource extends Resource
{
    protected static ?string $model = DataAws::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Data AWS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('device_id')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('data_timestamp'),
                Forms\Components\TextInput::make('wind_dir_deg_dd')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_speed_ff')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_speed_ff_maks')
                    ->numeric(),
                Forms\Components\TextInput::make('pressure_qfe_mb_derived')
                    ->numeric(),
                Forms\Components\TextInput::make('pressure_qff_mb_derived')
                    ->numeric(),
                Forms\Components\TextInput::make('temp_drybulb_c_tttttt')
                    ->numeric(),
                Forms\Components\TextInput::make('temp_dewpoint_c_tdtdtd')
                    ->numeric(),
                Forms\Components\TextInput::make('relative_humidity_pc')
                    ->numeric(),
                Forms\Components\TextInput::make('rainfall_last_mm')
                    ->numeric(),
                Forms\Components\TextInput::make('temp_max_c_txtxtx')
                    ->numeric(),
                Forms\Components\TextInput::make('temp_min_c_tntntn')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->defaultSort('data_timestamp', 'desc')
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->label('ID Perangkat')
                    ->copyable()
                    ->limit(8)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('data_timestamp')
                    ->label('Waktu (UTC)')
                    ->dateTime('Y-m-d H:i:s')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('wind_dir_deg_dd')
                    ->label('dd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('wind_speed_ff')
                    ->label('ff')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state !== null) {
                            // Konversi dari m/s ke knot
                            $windSpeed = $state * 1.94384;
                            return round($windSpeed);
                        }

                        return null;
                    }),
                Tables\Columns\TextColumn::make('wind_speed_ff_maks')
                    ->label('ff_maks')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state !== null) {
                            // Konversi dari m/s ke knot
                            $windSpeed = $state * 1.94384;
                            return round($windSpeed);
                        }

                        return null;
                    }),
                Tables\Columns\TextColumn::make('pressure_qfe_mb_derived')
                    ->label('qfe')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pressure_qff_mb_derived')
                    ->label('qff')
                    ->sortable(),
                Tables\Columns\TextColumn::make('temp_drybulb_c_tttttt')
                    ->label('t')
                    ->sortable(),
                Tables\Columns\TextColumn::make('temp_dewpoint_c_tdtdtd')
                    ->label('td')
                    ->sortable(),
                Tables\Columns\TextColumn::make('relative_humidity_pc')
                    ->label('rh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rainfall_last_mm')
                    ->label('rrr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('temp_max_c_txtxtx')
                    ->label('tx')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('temp_min_c_tntntn')
                    ->label('tn')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Update')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('Tanggal & Jam')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->displayFormat('d F Y'),

                        Forms\Components\Select::make('hour')
                            ->label('Jam')
                            ->searchable()
                            ->options(array_combine(range(0, 23), array_map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT), range(0, 23))))
                            ->placeholder('Semua jam')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['date'])) {
                            $query->whereDate('data_timestamp', $data['date']);
                        }

                        if (isset($data['hour'])) {
                            $query->whereRaw("HOUR(data_timestamp) = ?", [$data['hour']]);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?array {
                        $indicators = [];

                        if (!empty($data['date'])) {
                            $indicators[] = 'Tanggal: ' . Carbon::parse($data['date'])->format('d F Y');
                        }

                        if (!empty($data['hour'])) {
                            $indicators[] = 'Jam: ' . str_pad($data['hour'], 2, '0', STR_PAD_LEFT);
                        }

                        return $indicators ?: null;
                    }),


            ])
            ->deferFilters()

            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataAws::route('/'),
            'create' => Pages\CreateDataAws::route('/create'),
            'edit' => Pages\EditDataAws::route('/{record}/edit'),
        ];
    }
}
