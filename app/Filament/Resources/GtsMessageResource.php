<?php

namespace App\Filament\Resources;

use App\Filament\Components\GtsFormSchema;
use App\Filament\Resources\GtsMessageResource\Pages;
use App\Helpers\GtsMessageHelper;
use App\Models\GtsMessage;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class GtsMessageResource extends Resource
{
    protected static ?string $model = GtsMessage::class;

    // Icon navigasi yang lebih relevan dengan sistem komunikasi GTS
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Pesan GTS';

    protected static ?string $pluralModelLabel = 'Pesan GTS';

    protected static ?string $modelLabel = 'Pesan GTS';

    public static function form(Form $form): Form
    {
        return $form->schema(GtsFormSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('timestamp_data', 'desc')
            ->poll('60s')
            ->paginated([10, 25, 50, 100])
            ->searchOnBlur(true)
            ->deferLoading()
            ->recordUrl(null)

            // Tampilan jika data kosong
            ->emptyStateHeading('Belum Ada Pesan GTS')
            ->emptyStateDescription('Riwayat sandi cuaca yang tersimpan akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-inbox')

            ->columns([
                // 1. Jenis Pesan (Badge Warna Spesifik per Tipe)
                Tables\Columns\TextColumn::make('type_message')
                    ->label('Jenis Pesan')
                    ->state(fn($record) => GtsMessageHelper::getTypeMessageString($record->type_message))
                    ->badge()
                    ->color(fn(GtsMessage $record): string => match ((int) $record->type_message) {
                        1 => 'info',       // SINOPTIK = Biru / Sky
                        4 => 'warning',    // METAR = Kuning / Amber
                        5 => 'danger',     // SPECI = Merah / Rose
                        6 => 'success',    // TAF = Hijau Emerald
                        8 => 'primary',    // WXREV = Indigo / Ungu
                        53 => 'danger',    // DCH = Merah Rose
                        default => 'gray', // Tipe Lainnya = Abu-abu
                    }),

                // 2. Jam Pengamatan
                Tables\Columns\TextColumn::make('timestamp_data')
                    ->label('Jam Pengamatan (UTC)')
                    ->dateTime('d M Y - H:i')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->sortable(),

                // 3. Sandi GTS (Terminal Style: Bersih, Dynamic Accent & Auto-Wrap)
                Tables\Columns\TextColumn::make('sandi_gts')
                    ->label('Isi Sandi GTS')
                    ->fontFamily(FontFamily::Mono)
                    ->wrap()
                    ->copyable()
                    ->searchable()
                    ->extraAttributes(function (GtsMessage $record) {
                        $styleClasses = match ((int) $record->type_message) {
                            1 => 'border-sky-500 text-sky-300',          // SINOPTIK
                            4 => 'border-amber-500 text-amber-300',      // METAR
                            5 => 'border-rose-500 text-rose-300',        // SPECI
                            6 => 'border-emerald-500 text-emerald-300',  // TAF
                            8 => 'border-indigo-500 text-indigo-300',    // WXREV
                            53 => 'border-rose-500 text-rose-300',       // DCH
                            default => 'border-gray-500 text-gray-300',
                        };

                        return [
                            'class' => "font-mono text-xs bg-gray-950/80 p-2.5 rounded-md leading-relaxed select-all border-l-4 max-w-xl {$styleClasses}",
                        ];
                    }),

                // 4. Waktu Terkirim
                Tables\Columns\TextColumn::make('timestamp_sent_data')
                    ->label('Waktu Terkirim (UTC)')
                    ->dateTime('d M Y - H:i:s')
                    ->fontFamily(FontFamily::Mono)
                    ->placeholder('Belum Terkirim')
                    ->sortable()
                    ->toggleable(),

                // 5. Selisih Menit (Badge Indikator Ketepatan Waktu)
                Tables\Columns\TextColumn::make('delta_min')
                    ->label('Selisih')
                    ->state(function ($record) {
                        if ($record->timestamp_sent_data && $record->timestamp_data) {
                            $sentDataTime = Carbon::parse($record->timestamp_sent_data);
                            $dataTime = Carbon::parse($record->timestamp_data);
                            $diff = (int) $dataTime->diffInMinutes($sentDataTime, false);
                            return $diff . ' mnt';
                        }
                        return '—';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        if (!$record->timestamp_sent_data) return 'gray';

                        $val = abs((int) str_replace(' mnt', '', $state));
                        if ($val <= 10) return 'success'; // Ontime (<= 10 mnt)
                        if ($val <= 30) return 'warning'; // Telat Sedang
                        return 'danger';                 // Telat Parah (> 30 mnt)
                    })
                    ->toggleable(),

                // Kolom Audit (Hidden By Default)
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i:s')
                    ->fontFamily(FontFamily::Mono)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i:s')
                    ->fontFamily(FontFamily::Mono)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Filter::make('tanggal_pengamatan')
                    ->label('Tanggal Pengamatan')
                    ->form([
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->native(false)
                            ->closeOnDateSelection(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['tanggal'],
                            fn($q, $date) => $q->whereDate('timestamp_data', $date)
                        );
                    })
                    ->indicateUsing(function (array $data) {
                        if (! $data['tanggal']) {
                            return null;
                        }

                        $formatted = Carbon::parse($data['tanggal'])->translatedFormat('d F Y');
                        return "Tanggal: {$formatted}";
                    }),
            ])

            // Aksi Di-grouping Rapi Agar Kolom Tidak Sesak
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->tooltip('Menu Aksi'),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGtsMessages::route('/'),
            'create' => Pages\CreateGtsMessage::route('/create'),
            'edit' => Pages\EditGtsMessage::route('/{record}/edit'),
        ];
    }
}
