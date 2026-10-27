<?php

namespace App\Filament\Widgets;

use App\Filament\Components\GtsFormSchema;
use App\Jobs\SendGtsMessageJob;
use App\Models\GtsMessage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Helpers\GtsMessageHelper;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class PendingGtsMessages extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                GtsMessage::query()
                    ->where('timestamp_data', '>=', now()->subDays(2))
                    ->where('is_sent', 0)
            )
            ->heading('ANTRIAN PESAN GTS (BELUM TERKIRIM)')
            ->description('Daftar sandi cuaca yang sedang menunggu jadwal atau antrean pengiriman ke BMKGSwitching.')
            ->emptyStateHeading('Sempurna! Tidak ada pesan dalam antrian')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('timestamp_data')
            ->searchable(false)
            ->poll('15s')
            ->paginated(false)
            ->striped()

            ->columns([
                // 1. Jenis Pesan (Badge dengan Warna Spesifik untuk Setiap Tipe)
                Tables\Columns\TextColumn::make('type_message')
                    ->label('Jenis Pesan')
                    ->state(fn($record) => GtsMessageHelper::getTypeMessageString($record->type_message))
                    ->badge()
                    ->color(fn (GtsMessage $record): string => match ((int) $record->type_message) {
                        1 => 'info',       // SINOPTIK = Biru
                        4 => 'warning',    // METAR = Kuning / Amber
                        6 => 'success',    // TAF = Hijau Emerald
                        8 => 'primary',    // WXREV = Indigo / Ungu
                        53 => 'danger',    // DCH = Merah Rose
                        default => 'gray', // Tipe Lainnya = Abu-abu
                    }),

                // 2. Jam Pengamatan
                Tables\Columns\TextColumn::make('timestamp_data')
                    ->label('Jam Pengamatan (UTC)')
                    ->dateTime('d M Y - H:i')
                    ->fontFamily('mono')
                    ->sortable(),

                // 3. Sandi GTS (Gaya Terminal Modern dengan Aksen Warna Sesuai Tipe Pesan)
                Tables\Columns\TextColumn::make('sandi_gts')
                    ->label('Isi Sandi GTS')
                    ->fontFamily('mono')
                    ->wrap()
                    ->copyable()
                    ->extraAttributes(function (GtsMessage $record) {
                        // Memasang warna border dan teks terminal secara dinamis berdasarkan jenis pesan
                        $styleClasses = match ((int) $record->type_message) {
                            1 => 'border-sky-500 text-sky-300',          // SINOPTIK
                            4 => 'border-amber-500 text-amber-300',      // METAR
                            6 => 'border-emerald-500 text-emerald-300',  // TAF
                            8 => 'border-indigo-500 text-indigo-300',    // WXREV
                            53 => 'border-rose-500 text-rose-300',       // DCH
                            default => 'border-gray-500 text-gray-300',
                        };

                        return [
                            'class' => "font-mono text-[11px] bg-gray-950/80 p-2.5 rounded-md leading-relaxed select-all border-l-4 {$styleClasses}",
                        ];
                    }),

                // 4. Jadwal Pengiriman
                Tables\Columns\TextColumn::make('sent_schedule')
                    ->label('Jadwal Pengiriman')
                    ->state(function (GtsMessage $record) {
                        $timestamp = in_array($record->type_message, [8, 53])
                            ? Carbon::parse($record->timestamp_data)->subDay()
                            : Carbon::parse($record->timestamp_data);

                        return $timestamp->locale('id')->diffForHumans(parts: 2);
                    })
                    ->badge()
                    ->color(
                        fn(GtsMessage $record) =>
                        now()->gte(
                            in_array($record->type_message, [1, 4])
                                ? Carbon::parse($record->timestamp_data)->subDay()
                                : Carbon::parse($record->timestamp_data)
                        ) ? 'danger' : 'gray'
                    ),

                // 5. Mode Pengiriman
                Tables\Columns\TextColumn::make('sent_mode')
                    ->label('Mode')
                    ->state(function (GtsMessage $record) {
                        $sentMode = setting(GtsMessageHelper::getSentAndCreationSettingKey($record->type_message, 'sent_mode'));
                        return $sentMode == 1 ? 'Auto' : "Manual";
                    })
                    ->badge()
                    ->color(fn($state) => $state === 'Auto' ? 'success' : 'warning')
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('send')
                        ->label('Kirim Sekarang')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pengiriman Sandi')
                        ->modalDescription('Pastikan struktur sandi berikut sudah valid sebelum dilempar ke server pusat.')
                        ->action(function (GtsMessage $record) {
                            if (!$record) return;
                            if (now()->lt($record->timestamp_data)) {
                                Notification::make()->title('Belum waktunya pengiriman')->warning()->send();
                                return;
                            }
                            SendGtsMessageJob::dispatchSync($record->id, auth()->id());
                        }),

                    Tables\Actions\Action::make('edit')
                        ->label('Perbaiki Sandi')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->modalWidth('3xl')
                        ->form(GtsFormSchema::make())
                        ->fillForm(fn(GtsMessage $record): array => [
                            'type_message' => $record->type_message,
                            'sandi_gts' => $record->sandi_gts,
                            'timestamp_data' => $record->timestamp_data
                        ])
                        ->action(function (GtsMessage $record, $data) {
                            $record->update($data);
                            Notification::make()->title('Pesan berhasil diperbarui')->success()->send();
                        }),

                    Tables\Actions\DeleteAction::make('delete')
                        ->label('Batalkan/Hapus'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->tooltip('Menu Aksi')
                ->iconButton()
            ]);
    }
}
