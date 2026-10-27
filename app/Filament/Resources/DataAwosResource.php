<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataAwosResource\Pages;
use App\Filament\Resources\DataAwosResource\RelationManagers;
use App\Models\DataAwos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class DataAwosResource extends Resource
{
    protected static ?string $model = DataAwos::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Data AWOS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('device_id')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('data_timestamp'),
                Forms\Components\TextInput::make('wind_spd_2m_kt')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_dir_2m')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_gustav_10m_kt')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_spdx_2m_kt')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_spd_10m_kt')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_dir_10m')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_dirccw_10m')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_dircw_10m')
                    ->numeric(),
                Forms\Components\TextInput::make('wind_peak_10m_kt')
                    ->numeric(),
                Forms\Components\TextInput::make('at_5m_c')
                    ->numeric(),
                Forms\Components\TextInput::make('dp_5m_c')
                    ->numeric(),
                Forms\Components\TextInput::make('rh_5m')
                    ->numeric(),
                Forms\Components\TextInput::make('sr_6m_j_mncm2')
                    ->numeric(),
                Forms\Components\TextInput::make('qnh_1m_mb')
                    ->numeric(),
                Forms\Components\TextInput::make('qfe_1m_mb')
                    ->numeric(),
                Forms\Components\TextInput::make('bplow_1m_mb')
                    ->numeric(),
                Forms\Components\TextInput::make('pp_1h_mm')
                    ->numeric(),
                Forms\Components\TextInput::make('pp_6h_mm')
                    ->numeric(),
                Forms\Components\TextInput::make('sr_6m_w_m2')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->poll('60s')
            ->defaultSort('data_timestamp', 'desc')
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
                Tables\Columns\TextColumn::make('wind_dir_2m')
                    ->label('dd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('wind_spd_2m_kt')
                    ->label('ff')
                    ->sortable(),
                Tables\Columns\TextColumn::make('wind_gustav_10m_kt')
                    ->label('gust')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('at_5m_c')
                    ->label('t')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dp_5m_c')
                    ->label('td')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rh_5m')
                    ->label('rh')
                    ->sortable(),

                Tables\Columns\TextColumn::make('wind_spdx_2m_kt')
                    ->label('ffx')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('wind_dir_10m')
                    ->label('dd-10m')
                    ->sortable(),

                Tables\Columns\TextColumn::make('wind_spd_10m_kt')
                    ->label('ff-10m')
                    ->sortable(),

                Tables\Columns\TextColumn::make('wind_dirccw_10m')
                    ->toggleable(isToggledHiddenByDefault: true)

                    ->sortable(),
                Tables\Columns\TextColumn::make('wind_dircw_10m')
                    ->toggleable(isToggledHiddenByDefault: true)

                    ->sortable(),
                Tables\Columns\TextColumn::make('wind_peak_10m_kt')
                    ->label('ffx-10m')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sr_6m_j_mncm2')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('qnh_1m_mb')
                    ->label('qnh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('qfe_1m_mb')
                    ->label('qfe')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bplow_1m_mb')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pp_1h_mm')
                    ->label('rrr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pp_6h_mm')
                    ->label('rrr6')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sr_6m_w_m2')
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
            'index' => Pages\ListDataAwos::route('/'),
            'create' => Pages\CreateDataAwos::route('/create'),
            'edit' => Pages\EditDataAwos::route('/{record}/edit'),
        ];
    }
}
