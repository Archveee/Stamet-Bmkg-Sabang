<?php

namespace App\Filament\Widgets;

use App\Models\GtsMonitoring;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class GtsDailyMonitoringTable extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.gts-daily-monitoring-table';

    public ?array $data = [];
    public int $month;
    public int $year;
    public ?array $daysInMonth = [];
    public Collection $monitoringData;

    protected int $defaultColumnSpan = 3;

    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('month')
                        ->label('Bulan')
                        ->options([
                            '1' => 'Januari',
                            '2' => 'Februari',
                            '3' => 'Maret',
                            '4' => 'April',
                            '5' => 'Mei',
                            '6' => 'Juni',
                            '7' => 'Juli',
                            '8' => 'Agustus',
                            '9' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ])
                        ->default(fn() => $this->month)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->month = (int)$state;
                            $this->loadData();
                        }),

                    TextInput::make('year')
                        ->label('Tahun')
                        ->type('number')
                        ->default(fn() => $this->year)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->year = (int)$state;
                            $this->loadData();
                        }),
                ]),
        ];
    }

    public function mount(): void
    {
        $this->month = (int)Carbon::now()->month;
        $this->year = (int)Carbon::now()->year;
        $this->loadData();
    }

    public function loadData(): void
    {
        // Generate array of days in month
        $daysInMonth = Carbon::createFromDate($this->year, $this->month, 1)->daysInMonth;
        $this->daysInMonth = range(1, $daysInMonth);

        // Load GTS monitoring data for selected month/year
        $this->monitoringData = GtsMonitoring::query()
            ->whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->day;
            });

        // Format data as needed
        $this->data = [
            'sinoptik_sent' => $this->formatMonitoringData('sinoptik_sent_percent'),
            'sinoptik_ontime' => $this->formatMonitoringData('sinoptik_ontime_percent'),
            'sinoptik_valid' => $this->formatMonitoringData('sinoptik_valid_percent'),
            'metar_sent' => $this->formatMonitoringData('metar_sent_percent'),
            'metar_ontime' => $this->formatMonitoringData('metar_ontime_percent'),
            'metar_valid' => $this->formatMonitoringData('metar_valid_percent'),
            'taf_sent' => $this->formatMonitoringData('taf_sent_percent'),
            'taf_ontime' => $this->formatMonitoringData('taf_ontime_percent'),
            'taf_valid' => $this->formatMonitoringData('taf_valid_percent'),
        ];
    }

    private function formatMonitoringData(string $attribute): array
    {
        $result = [];
        foreach ($this->daysInMonth as $day) {
            $monitoring = $this->monitoringData->get($day);
            $result[$day] = $monitoring ? $monitoring->$attribute : null;
        }
        return $result;
    }

    public function getMonthName(): string
    {
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $monthNames[$this->month] ?? '';
    }
}
