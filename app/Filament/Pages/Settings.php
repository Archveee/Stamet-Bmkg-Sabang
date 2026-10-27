<?php

namespace App\Filament\Pages;

use App\Filament\Components\SentAndCreationModeSettingFormSchema;
use App\Models\Setting;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms; // 🛡️ REQUIRED IN V3
use Filament\Forms\Concerns\InteractsWithForms; // 🛡️ REQUIRED IN V3
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-m-adjustments-horizontal';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = -10;

    protected static ?string $title = 'Pengaturan';

    public ?array $data = [];

    public function mount(): void
    {
        $data = Setting::get();
        $data = $data->pluck('setting_value', 'setting_name')->toArray();
        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make()
                    ->schema([
                        Section::make('Data Stasiun')
                            ->columns(3)
                            ->collapsible()
                            ->schema([
                                TextInput::make('station_name')
                                    ->label('Nama Stasiun')
                                    ->columnSpanFull(),
                                TextInput::make('station_wmo_id')
                                    ->label('Kode WMO'),
                                TextInput::make('station_icao_id')
                                    ->label('Kode ICAO'),
                                TextInput::make('station_latitude')
                                    ->label('Letak Garis Lintang'),
                                TextInput::make('station_longitude')
                                    ->label('Letak Garis Bujur'),
                                TextInput::make('station_operation_hour')
                                    ->label('Jam Beroperasi')
                                    ->suffix(' jam'),
                                TextInput::make('station_utc_diff')
                                    ->label('Selisih dengan UTC')
                                    ->suffix(' jam'),
                            ]),

                        Section::make()
                            ->heading('Konfigurasi BMKGSATU')
                            ->columns(4)
                            ->collapsible()
                            ->schema([
                                Section::make('Kredensial & URL')
                                    ->columns(2)
                                    ->collapsed()
                                    ->schema([
                                        TextInput::make('bmkgsatu_username')
                                            ->label('Username'),
                                        TextInput::make('bmkgsatu_password')
                                            ->label('Sandi')
                                            ->password()
                                            ->revealable(),
                                        TextInput::make('bmkgsatu_base_url')
                                            ->label('Base URL')
                                            ->placeholder('https://')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Meteorologi Sinoptik')
                                    ->collapsed()
                                    ->columns(3)
                                    ->schema([
                                        ToggleButtons::make('bmkgsatu_meteorologi_sinoptik_sent_mode')
                                            ->label('Mode Pengiriman')
                                            ->options([true => 'Auto', false => 'Manual'])
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped(),
                                        ToggleButtons::make('bmkgsatu_meteorologi_sinoptik_gts_hourly')
                                            ->label('Frekuensi Pengiriman GTS')
                                            ->options([
                                                1 => 'Per jam',
                                                0 => 'Per 3 jam'
                                            ])
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                    ]),

                                Section::make('Mode Pembuatan & Pengiriman GTS')
                                    ->collapsed()
                                    ->schema(SentAndCreationModeSettingFormSchema::make()),

                                Section::make('Tipe Pesan')
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('bmkgsatu_type_message')
                                            ->label('')
                                            ->rows(10)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Data Heading')
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('bmkgsatu_heading_list')
                                            ->label('')
                                            ->rows(10)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Daftar Observer')
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('bmkgsatu_observer_list')
                                            ->label('')
                                            ->rows(10)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Section::make('Konfigurasi Excel')
                            ->columns(3)
                            ->collapsible()
                            ->schema([
                                Section::make('Umum')
                                    ->collapsed()
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('excel_sinoptik_path')
                                            ->label('Path Folder Synop')
                                            ->placeholder('D:\\Synop\\')
                                            ->columnSpanFull(),
                                        TextInput::make('excel_row_distance')
                                            ->label('Jarak Antar Baris'),
                                        TextInput::make('excel_row_distance_multipage')
                                            ->label('Jarak Antar Baris Multi Halaman'),
                                        ToggleButtons::make('excel_daily')
                                            ->label('File Perhari/Bulan')
                                            ->grouped()
                                            ->options([
                                                'day' => 'Hari',
                                                'month' => 'Bulan',
                                            ]),
                                    ]),
                                Section::make('Server Excel')
                                    ->collapsed()
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('excel_server_url')
                                            ->label('Alamat Server Excel'),
                                        TextInput::make('excel_server_token')
                                            ->label('Token')
                                            ->password()
                                            ->revealable(),
                                        TextInput::make('excel_server_sinoptik_creation_minute')
                                            ->label('Menit Pembuatan Sinop + 1 menit')
                                            ->numeric()
                                            ->minValue(50)
                                            ->maxValue(59)
                                    ]),

                                Section::make('Konfigurasi Cell')
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('excel_cell_config')
                                            ->label('')
                                            ->rows(10)
                                            ->json()
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Nilai Default')
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('excel_default_value')
                                            ->label('Nilai Default')
                                            ->rows(5)
                                            ->json()
                                            ->columnSpanFull()
                                    ]),
                            ]),

                        Section::make()
                            ->heading('Konfigurasi BMKGSabang')
                            ->columns(4)
                            ->collapsed()
                            ->schema([
                                Section::make('Schedule')
                                    ->collapsible()
                                    ->columns(4)
                                    ->schema([
                                        ToggleButtons::make('bmkgsabang_generate_sinoptik')
                                            ->label('Generate Sinoptik')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_generate_metar')
                                            ->label('Generate Metar')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_generate_wxrev')
                                            ->label('Generate WXREV')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_generate_dch')
                                            ->label('Generate Data Cuaca Harian')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_send_meteorologi_sinoptik')
                                            ->label('Send Meteorologi Sinoptik')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_gts_send_pending_messages')
                                            ->label('Send GTS Pending Messages')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_awsmaritim_fetch_data')
                                            ->label('Fetch AWS Maritim')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_sinoptik_fetch_screenshot')
                                            ->label('Fetch Screenshot PC Sinoptik')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                    ]),

                                Section::make('Notifikasi Whatsapp')
                                    ->collapsible()
                                    ->columns(5)
                                    ->schema([
                                        ToggleButtons::make('bmkgsabang_whatsapp_notification_fetch_sinoptik_error')
                                            ->label('Gagal menarik data sinoptik')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_whatsapp_notification_monitoring_sinoptik_not_found')
                                            ->label('Form sinoptik belum diisi di bmkgsatu')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                        ToggleButtons::make('bmkgsabang_whatsapp_notification_gts_not_found')
                                            ->label('GTS tidak terkirim ke bmkgsatu')
                                            ->colors([true => 'info', false => 'warning'])
                                            ->grouped()
                                            ->options([1 => 'ON', 0 => 'OFF']),
                                    ])
                            ]),

                        Section::make('Konfigurasi Sumber Data Otomatis')
                            ->columns(2)
                            ->collapsed()
                            ->schema([
                                TextInput::make('aws_server_url')
                                    ->label('Alamat Server AWS'),
                                TextInput::make('aws_server_token')
                                    ->label('Token AWS')
                                    ->password()
                                    ->revealable(),
                                TextInput::make('awos_server_url')
                                    ->label('Alamat Server AWOS'),
                                TextInput::make('awos_server_token')
                                    ->label('Token AWOS')
                                    ->password()
                                    ->revealable(),
                            ]),

                        Section::make('Kode Referensi')
                            ->collapsible()
                            ->schema([
                                Textarea::make('reference_ww')->label('Keadaan Cuaca Waktu Pengamatan')->json()->rows(5),
                                Textarea::make('reference_w1w2')->label('Keadaan Cuaca Waktu Yang Lalu')->json()->rows(5),
                                Textarea::make('reference_cloud_type')->label('Jenis Awan')->rows(5),
                                Textarea::make('reference_cl_type')->label('Jenis Awan Rendah')->rows(5),
                                Textarea::make('reference_cm_type')->label('Jenis Awan Menengah')->rows(5),
                                Textarea::make('reference_ch_type')->label('Jenis Awan Tinggi')->rows(5),
                                Textarea::make('reference_ec')->label('Sudut Puncak Awan')->json()->rows(5),
                                Textarea::make('reference_cloud_layer_type')->label('Lapisan Awan')->json()->rows(5),
                                Textarea::make('reference_cloud_dir')->label('Arah Awan')->json()->rows(5),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->requiresConfirmation()
                ->modalHeading('Simpan Data')
                ->modalDescription('Pastikan data yang diisi sudah benar')
                ->modalSubmitActionLabel('Simpan')
                ->color('warning')
                ->icon('heroicon-o-inbox-arrow-down')
                ->form([
                    TextInput::make('confirmation_text')
                        ->label('Konfirmasi')
                        ->required()
                        ->placeholder('Ketik CONFIRM')
                        // 🛡️ FIXED: Menggunakan standardisasi callback validator penulisan mesin Laravel terbaru
                        ->rules([
                            function (string $attribute, $value, Closure $fail) {
                                if ($value !== 'CONFIRM') {
                                    $fail('Ups, salah!');
                                }
                            },
                        ])
                ])
                ->action(function () {
                    $data = $this->form->getState();
                    foreach ($data as $key => $val) {
                        Setting::updateOrCreate(
                            ['setting_name' => $key],
                            ['setting_value' => $val]
                        );
                    }

                    cache()->forget('settings');

                    Notification::make()
                        ->title('Pengaturan berhasil disimpan!')
                        ->success()
                        ->send();
                })
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        // 🛡️ FIXED: Proteksi Null-safe operator untuk menghindari error fatal Guest user/Expired Session
        return auth()->user()?->isAdmin() ?? false;
    }
}
