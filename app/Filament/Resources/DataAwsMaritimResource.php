<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataAwsMaritimResource\Pages;
use App\Models\DataAwsMaritim;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DataAwsMaritimResource extends Resource
{
    protected static ?string $model = DataAwsMaritim::class;

    protected static ?string $slug = 'data-aws-maritim';

    protected static ?string $navigationLabel = 'Data AWS Maritim';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('idaws')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('waktu'),
                Forms\Components\TextInput::make('windspeed')
                    ->numeric(),
                Forms\Components\TextInput::make('winddir')
                    ->numeric(),
                Forms\Components\TextInput::make('temp')
                    ->numeric(),
                Forms\Components\TextInput::make('rh')
                    ->numeric(),
                Forms\Components\TextInput::make('pressure')
                    ->numeric(),
                Forms\Components\TextInput::make('rain')
                    ->numeric(),
                Forms\Components\TextInput::make('solrad')
                    ->numeric(),
                Forms\Components\TextInput::make('netrad')
                    ->numeric(),
                Forms\Components\TextInput::make('watertemp')
                    ->numeric(),
                Forms\Components\TextInput::make('waterlevel')
                    ->numeric(),
                Forms\Components\TextInput::make('ta_min')
                    ->numeric(),
                Forms\Components\TextInput::make('ta_max')
                    ->numeric(),
                Forms\Components\TextInput::make('pancilevel')
                    ->numeric(),
                Forms\Components\TextInput::make('pancitemp')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->defaultSort('waktu', 'desc')
            ->poll('60s')
            ->searchOnBlur(true)
            ->columns([
                Tables\Columns\TextColumn::make('idaws')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('waktu')
                    ->label('Waktu (UTC)')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('winddir')
                    ->label('dd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('windspeed')
                    ->label('ff')
                    ->sortable(),

                Tables\Columns\TextColumn::make('temp')
                    ->label('t')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rh')
                    ->label('rh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pressure')
                    ->label('P')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rain')
                    ->label('rrr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('solrad')
                    ->toggleable(isToggledHiddenByDefault: true)

                    ->sortable(),
                Tables\Columns\TextColumn::make('netrad')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('watertemp')
                    ->label('wt')
                    ->toggleable(isToggledHiddenByDefault: false)

                    ->sortable(),
                Tables\Columns\TextColumn::make('waterlevel')
                    ->label('wl')
                    ->toggleable(isToggledHiddenByDefault: false)

                    ->sortable(),
                Tables\Columns\TextColumn::make('ta_min')
                    ->label('tn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ta_max')
                    ->label('tx')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pancilevel')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('pancitemp')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
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
                            $query->whereDate('waktu', $data['date']);
                        }

                        if (isset($data['hour'])) {
                            $query->whereRaw("HOUR(waktu) = ?", [$data['hour']]);
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
            'index' => Pages\ListDataAwsMaritims::route('/'),
            'create' => Pages\CreateDataAwsMaritim::route('/create'),
            'edit' => Pages\EditDataAwsMaritim::route('/{record}/edit'),
        ];
    }
}
