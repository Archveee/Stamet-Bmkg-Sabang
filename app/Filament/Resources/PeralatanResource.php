<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeralatanResource\Pages;
use App\Models\Peralatan;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PeralatanResource extends Resource
{
    protected static ?string $model = Peralatan::class;

    protected static ?string $slug = 'peralatan';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(2)
                    ->schema([

                        Forms\Components\TextInput::make('nama_alat')
                            ->label('Nama Alat')
                            ->required(),
                        Forms\Components\TextInput::make('merk')
                            ->label('Merk'),
                        Forms\Components\TextInput::make('tipe')
                            ->label('Tipe'),
                        Forms\Components\TextInput::make('tahun_pengadaan')
                            ->label('Tahun Pengadaan')
                            ->numeric(),
                        Forms\Components\Select::make('kondisi')
                            ->label('Kondisi')
                            ->placeholder('Pilih kondisi')
                            ->options([
                                "B" => "Baik",
                                "R" => "Rusak",
                                "RB" => "Rusak Berat"
                            ],),

                        Forms\Components\DateTimePicker::make('last_update')
                            ->label('Terakhir Update')
                            ->native(false)
                            ->maxDate(now())
                            ->suffix('UTC')
                            ->displayFormat('d F Y H:i'),

                        Forms\Components\TextInput::make('sort')
                            ->label('Urutan Monitoring')
                            ->numeric(),

                        Forms\Components\Toggle::make('monitoring_tidak')
                            ->label('Monitor Peralatan')
                            ->columnSpanFull(),

                        // Forms\Components\FileUpload::make('image')
                        //     ->label('Gambar Monitoring Terbaru')
                        //     ->image()
                        //     ->openable()
                        //     ->columnSpanFull(),

                        Forms\Components\Textarea::make('keterangan')
                            ->rows(3)
                            ->columnSpanFull(),

                    ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->copyable()
                    ->limit(8),
                Tables\Columns\TextColumn::make('sort')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_alat')
                    ->label('Nama Alat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('merk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kondisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_pengadaan')
                    ->label('Tahun Pengadaan')
                    ->sortable(),
                Tables\Columns\IconColumn::make('monitoring_tidak')
                    ->label('Monitor')
                    ->boolean(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan'),
                Tables\Columns\TextColumn::make('last_update')
                    ->label('Data Terakhir')
                    ->dateTime('d F Y H:i')
                    ->sortable(),

                // Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPeralatans::route('/'),
            'create' => Pages\CreatePeralatan::route('/create'),
            'edit' => Pages\EditPeralatan::route('/{record}/edit'),
        ];
    }
}
