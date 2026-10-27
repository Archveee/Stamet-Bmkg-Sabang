<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObserverDutyResource\Pages;
use App\Helpers\SinoptikDataHelper;
use App\Models\ObserverDuty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class ObserverDutyResource extends Resource
{
    protected static ?string $model = ObserverDuty::class;

    protected static ?string $navigationLabel = 'Observer on Duty';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->default(now())
                    ->displayFormat('d F Y'),
                Forms\Components\Select::make('observer_1')
                    ->label('Observer 1')
                    ->options(function () {
                        $observersData = setting('bmkgsatu_observer_list');
                        $observers = [];
                        foreach ($observersData as $observer) {
                            $observers[$observer['observer_id']] = $observer['observer_name'];
                        }

                        return $observers;
                    }),
                Forms\Components\Select::make('observer_2')
                    ->label('Observer 2')
                    ->options(function () {
                        $observersData = setting('bmkgsatu_observer_list');
                        $observers = [];
                        foreach ($observersData as $observer) {
                            $observers[$observer['observer_id']] = $observer['observer_name'];
                        }

                        return $observers;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('Y-m-d')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('observer_1')
                    ->label('Observer Pagi')
                    ->sortable()
                    ->formatStateUsing(fn($state) => SinoptikDataHelper::getObserverNameFromId($state)),
                Tables\Columns\TextColumn::make('observer_2')
                    ->label('Observer Malam')
                    ->sortable()
                    ->formatStateUsing(fn($state) => SinoptikDataHelper::getObserverNameFromId($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()->isAdmin())
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ])
                    ->visible(fn() => auth()->user()->isAdmin())
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageObserverDuties::route('/'),
        ];
    }
}
