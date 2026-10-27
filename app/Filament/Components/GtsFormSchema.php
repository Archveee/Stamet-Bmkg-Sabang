<?php

namespace App\Filament\Components;

use App\Helpers\DateHelper;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\HtmlString;

class GtsFormSchema
{
    public static function make()
    {
        return [
            Textarea::make('sandi_gts')
                ->label('Sandi')
                ->required()
                ->placeholder('Masukkan sandi yang akan dikirimkan')
                ->rows(5)
                ->autosize()
                ->columnSpanFull()
                ->extraAttributes([
                    'x-data' => '{
                        isCompleteSpeci: false,
                        hasNotified: false,
                        init() {
                            this.$watch("$wire.data.sandi_gts", (value) => {
                                let val = (value || "").toUpperCase().trim();
                                
                                // Deteksi apakah pesan memuat SPECI dan diakhiri/mengandung tanda =
                                if (val.includes("SPECI") && val.includes("=")) {
                                    this.isCompleteSpeci = true;
                                    
                                    // Kirim notifikasi toast hanya sekali saat sandi dinyatakan lengkap
                                    if (!this.hasNotified) {
                                        this.hasNotified = true;
                                        new FilamentNotification()
                                            .title("⚡ Sandi SPECI Lengkap!")
                                            .body("Sandi SPECI telah siap. Periksa kembali dan segera tekan tombol Submit.")
                                            .warning()
                                            .duration(7000)
                                            .send();
                                    }
                                } else {
                                    this.isCompleteSpeci = false;
                                    this.hasNotified = false;
                                }
                            });
                        }
                    }',
                ])
                ->helperText(new HtmlString('
                    <template x-if="isCompleteSpeci">
                        <div class="mt-3 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 shadow-sm flex items-start gap-3 transition-all duration-300">
                            <div class="p-2 bg-amber-500/10 dark:bg-amber-500/20 rounded-lg text-amber-600 dark:text-amber-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                    Pesan SPECI Lengkap Terdeteksi!
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100">
                                        Siap Kirim Instan
                                    </span>
                                </h4>
                                <p class="text-xs text-amber-700 dark:text-amber-300 mt-1 leading-relaxed">
                                    Pastikan format dan isi sandi sudah benar. <strong>Segera tekan tombol Submit</strong> di bawah agar pesan langsung dikirimkan secara instan tanpa antrian.
                                </p>
                            </div>
                        </div>
                    </template>
                ')),

            Select::make('type_message')
                ->label('Jenis Pesan')
                ->placeholder('Pilih salah satu')
                ->options(setting('bmkgsatu_type_message'))
                ->native(false)
                ->hiddenOn('create')
                ->dehydratedWhenHidden(true)
                ->columnSpanFull(),

            DateTimePicker::make('timestamp_data')
                ->label('Jam Pengamatan (UTC)')
                ->native(false)
                ->suffix('UTC')
                ->timezone('UTC')
                ->default(DateHelper::getNearestSentHour())
                ->seconds(false)
                ->hiddenOn('create')
                ->dehydratedWhenHidden()
                ->displayFormat('Y-m-d H:i')
                ->columnSpanFull()
        ];
    }
}
