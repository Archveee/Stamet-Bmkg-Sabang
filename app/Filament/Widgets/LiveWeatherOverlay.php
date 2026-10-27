<?php

namespace App\Filament\Widgets;

use App\Helpers\SinoptikDataHelper;
use App\Models\DataAws;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class LiveWeatherOverlay extends Widget
{
    protected static string $view = 'filament.widgets.live-weather-overlay';
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = false;

    public function getViewData(): array
    {
        $latest = DataAws::latest('data_timestamp')->first();

        if (!$latest) {
            return [];
        }

        // Waktu sekarang (UTC)
        $endTime = Carbon::parse($latest->data_timestamp);
        $startTime = $endTime->copy()->subHour(); // mundur 1 jam

        // Hitung akumulasi curah hujan 1 jam terakhir
        $rainLastHour = DataAws::whereBetween('data_timestamp', [$startTime, $endTime])
            ->sum('rainfall_last_mm');

        $wind = SinoptikDataHelper::normalizeWindData(
            $latest->wind_speed_ff,
            $latest->wind_dir_deg_dd,
            $latest->data_timestamp
        );

        return [
            'timestamp' => $endTime->addHours(7)->format('Y-m-d H:i:s') . ' WIB',
            'wind_dir_deg_dd' => $wind['wind_dir_deg_dd'],
            'wind_speed_ff' => $wind['wind_speed_ff'],
            'wind_speed_ff_maks' => (int) round(($latest->wind_speed_ff_maks ?? 0) * 1.94384),
            'temp_dry' => $latest->temp_drybulb_c_tttttt,
            'temp_dew' => $latest->temp_dewpoint_c_tdtdtd,
            'humidity' => $latest->relative_humidity_pc,
            'qnh' => $latest->pressure_qff_mb_derived,
            'qfe' => $latest->pressure_qfe_mb_derived,
            'rain' => $latest->rainfall_last_mm > 0 ? round($latest->rainfall_last_mm, 1) . ' mm' : '-',
            'rain_last_hour' => $rainLastHour > 0 ? round($rainLastHour, 1) . ' mm' : '-',
        ];
    }
}
