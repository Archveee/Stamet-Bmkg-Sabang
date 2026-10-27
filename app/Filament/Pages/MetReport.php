<?php

namespace App\Filament\Pages;

use App\Models\DataAwos;
use App\Models\DataSinoptik;
use App\Models\User;
use App\Services\MetReportImageService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class MetReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Other';
    protected static ?string $navigationLabel = 'Met Report';
    protected static ?string $title = 'MET REPORT / QAM GENERATOR';
    protected static string $view = 'filament.pages.met-report';

    public ?array $data = [];
    public ?string $previewImageUrl = null;

    public function mount(): void
    {
        $now = now()->setTimezone('UTC');

        $this->form->fill([
            'date' => $now->format('Y-m-d'),
            'hour' => $now->format('H'),
            'minute' => $now->format('i'),
            'observer' => auth()->user()->name ?? 'OBSERVER',
            'aerodrome' => 'WITN',
            'weather' => 'NIL',
            'rvr' => '-',
            'remark' => '-',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('1. Waktu & Observer')
                    ->columns(4)
                    ->schema([
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required(),
                        TextInput::make('hour')
                            ->label('Jam UTC')
                            ->placeholder('07')
                            ->required(),
                        TextInput::make('minute')
                            ->label('Menit')
                            ->placeholder('00')
                            ->required(),
                        Select::make('observer')
                            ->label('Observer')
                            ->options(fn () => User::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->createOptionUsing(fn (string $input) => trim($input))
                            ->required(),
                    ]),

                Section::make('2. Data Observasi & Meteorologi')
                    ->columns(3)
                    ->schema([
                        TextInput::make('wind')
                            ->label('Surface Wind')
                            ->placeholder('cth: 22003'),

                        TextInput::make('visibility')
                            ->label('Visibility')
                            ->placeholder('cth: 9'),

                        TextInput::make('rvr')
                            ->label('Runway Visual Range')
                            ->placeholder('-')
                            ->default('-'),

                        TextInput::make('weather')
                            ->label('Present Weather')
                            ->placeholder('NIL')
                            ->default('NIL'),

                        TextInput::make('cloud')
                            ->label('Low Cloud')
                            ->placeholder('cth: 8 080 atau 050'),

                        TextInput::make('temperature')
                            ->label('Air Temp (°C)')
                            ->placeholder('28'),

                        TextInput::make('dew_point')
                            ->label('Dew Point (°C)')
                            ->placeholder('25'),

                        TextInput::make('qnh')
                            ->label('QNH (MB)')
                            ->placeholder('1010.5'),

                        TextInput::make('qfh')
                            ->label('QFE (MB)')
                            ->placeholder('1008.2'),

                        TextInput::make('remark')
                            ->label('Remark')
                            ->placeholder('-')
                            ->default('-')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    private function getTargetCarbonInstance(): Carbon
    {
        $formData = $this->form->getState();

        $date = $formData['date'] ?? now()->format('Y-m-d');
        $hour = str_pad($formData['hour'] ?? '00', 2, '0', STR_PAD_LEFT);
        $minute = str_pad($formData['minute'] ?? '00', 2, '0', STR_PAD_LEFT);

        return Carbon::parse("{$date} {$hour}:{$minute}", 'UTC');
    }

    public function autoFillFromDatabase(): void
    {
        $targetTime = $this->getTargetCarbonInstance();

        $awos = DataAwos::where('data_timestamp', '<=', $targetTime)
            ->orderBy('data_timestamp', 'desc')
            ->first();

        $sinop = DataSinoptik::where('data_timestamp', '<=', $targetTime)
            ->orderBy('data_timestamp', 'desc')
            ->first();

        $updates = [];

        // 1. Data dari AWOS (Fungsi round() dihapus agar angka desimal tidak hilang)
        if ($awos) {
            if (!is_null($awos->wind_dir_10m) && !is_null($awos->wind_spd_10m_kt)) {
                $dir = str_pad((string) round($awos->wind_dir_10m), 3, '0', STR_PAD_LEFT);
                $spd = str_pad((string) round($awos->wind_spd_10m_kt), 2, '0', STR_PAD_LEFT);
                $updates['wind'] = $dir . $spd;
            }

            if (!is_null($awos->at_5m_c)) {
                $updates['temperature'] = (string) $awos->at_5m_c;
            }
            if (!is_null($awos->dp_5m_c)) {
                $updates['dew_point'] = (string) $awos->dp_5m_c;
            }
            if (!is_null($awos->qnh_1m_mb)) {
                $updates['qnh'] = (string) $awos->qnh_1m_mb;
            }
            if (!is_null($awos->qfe_1m_mb)) {
                $updates['qfh'] = (string) $awos->qfe_1m_mb;
            }
        }

        // 2. Data dari Sinoptik
        if ($sinop) {
            if (!is_null($sinop->visibility_vv)) {
                $updates['visibility'] = (string) $sinop->visibility_vv;
            }

            $okta = $sinop->cloud_low_cover_oktas
                ?? $sinop->cloud_low_cover_1
                ?? $sinop->cloud_layer_1_amt_oktas_ns;

            $heightMeter = $sinop->cloud_low_base_1
                ?? $sinop->cloud_layer_1_height_m_hshs;

            if (!is_null($okta) && !is_null($heightMeter) && $okta > 0) {
                $heightHundredsFeet = round($heightMeter / 30.48);
                $heightFormatted = str_pad((string) $heightHundredsFeet, 3, '0', STR_PAD_LEFT);
                $updates['cloud'] = "{$okta} {$heightFormatted}";
            } elseif (!is_null($okta) && $okta == 0) {
                $updates['cloud'] = 'NSC';
            }
        }

        $this->form->fill(array_merge($this->form->getState(), $updates));

        Notification::make()
            ->title('Data AWOS & Sinoptik Berhasil Dimuat')
            ->body('Mengambil data sensor s.d. ' . $targetTime->format('Y-m-d H:i') . ' UTC')
            ->success()
            ->send();
    }

    private function buildReportData(): array
    {
        $state = $this->form->getState();
        $dt = $this->getTargetCarbonInstance();

        return [
            'date' => strtoupper($dt->format('d M Y')),
            'time' => $dt->format('Hi') . ' UTC',
            'issue_time' => strtoupper($dt->format('d M Y H:i')) . ' UTC',
            'aerodrome' => $state['aerodrome'] ?? 'WITN',
            'wind' => $state['wind'] ?? '-',
            'visibility' => $state['visibility'] ?? '-',
            'rvr' => $state['rvr'] ?? '-',
            'weather' => $state['weather'] ?? 'NIL',
            'cloud' => $state['cloud'] ?? '-',
            'temperature' => $state['temperature'] ?? '-',
            'dew_point' => $state['dew_point'] ?? '-',
            'qnh' => $state['qnh'] ?? '-',
            'qfh' => $state['qfh'] ?? '-',
            'remark' => $state['remark'] ?? '-',
            'observer' => strtoupper($state['observer'] ?? '-'),
        ];
    }

    public function generateAndOpenModal(): void
    {
        $reportData = $this->buildReportData();
        $filename = 'preview_' . auth()->id() . '.jpg';
        $outputPath = storage_path('app/public/met_reports/' . $filename);

        MetReportImageService::generateJpeg($reportData, $outputPath);

        $this->previewImageUrl = asset('storage/met_reports/' . $filename) . '?v=' . time();

        Notification::make()
            ->title('Harap Periksa Kembali!')
            ->body('Pastikan seluruh parameter pada hasil laporan sudah sesuai sebelum diunduh.')
            ->warning()
            ->persistent()
            ->send();

        $this->dispatch('open-modal', id: 'preview-modal');
    }

    public function downloadJpeg()
    {
        $dt = $this->getTargetCarbonInstance();
        $reportData = $this->buildReportData();

        $filename = 'MET_REPORT_' . $dt->format('Ymd_Hi') . '.jpg';
        $outputPath = storage_path('app/public/met_reports/' . $filename);

        MetReportImageService::generateJpeg($reportData, $outputPath);

        return response()->download($outputPath);
    }
}
