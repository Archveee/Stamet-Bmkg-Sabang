<?php

namespace App\Services;

use App\Helpers\WxrevDataHelper;
use App\Models\DataSinoptik;
use Carbon\Carbon;

class WxrevComposer
{
    public static function composeWxrevMessage(Carbon $date)
    {
        // Ambil setting heading untuk WXREV
        $wxrevHeadingCode = setting('bmkgsatu_heading_list')['wxrev'] ?? 'MMID67';
        $stationIcao = setting('station_icao_id', 'WITN');

        // Format waktu dalam UTC/GMT (misalnya "240000" untuk tanggal 24 jam 00:00)
        $headingTime = $date->copy()->addDay()->format('dHi');

        // Ambil data operasional dan kode stasiun
        $operatingHour = setting('station_operation_hour', 24);
        $stnCode = setting('station_wmo_id', 96001);

        // **Hanya 2 Query**: Ambil semua data 24 jam + data jam 00:00 hari berikutnya
        $dataSinoptik = DataSinoptik::whereDate('data_timestamp', $date)
            ->select([
                'temp_max_c_txtxtx',
                'pressure_qff_mb_derived',
                'pressure_qfe_mb_derived',
                'relative_humidity_pc',
                'wind_dir_deg_dd',
                'wind_speed_ff'
            ])
            ->get();


        $dataSinoptikNextDay = DataSinoptik::where('data_timestamp', $date->copy()->addDay()->startOfDay())
            ->select([
                'temp_min_c_tntntn',
                'rainfall_24h_rrrr'
            ])
            ->first();

        if ($dataSinoptik->isEmpty() || !$dataSinoptikNextDay) {
            logger("Data sinoptik tidak lengkap untuk GTS WXREV.");
            return;
        }

        // Generate bagian-bagian pesan WXREV
        $mmyygp     = WxrevDataHelper::createMmyyGp($date, $operatingHour);
        $arTxTxTnTn = WxrevDataHelper::createArTxTxTnTn($dataSinoptik, $dataSinoptikNextDay);
        $arRRRR     = WxrevDataHelper::createArRRRR($dataSinoptikNextDay);
        $apPxPxPnPn = WxrevDataHelper::createApPxPxPnPn($dataSinoptik);
        $aUxUxUUn   = WxrevDataHelper::createAUxUxUUn($dataSinoptik);
        $rDrDDfmfm  = WxrevDataHelper::createrDrDDfmfm($dataSinoptik, $operatingHour);

        // Susun heading WXREV
        $wxrevHeading = "{$wxrevHeadingCode} {$stationIcao} {$headingTime}";

        // Susun pesan WXREV
        $wxrevMessage = "WXREV {$mmyygp}\n{$stnCode} {$arTxTxTnTn} {$apPxPxPnPn} {$aUxUxUUn} {$arRRRR} {$rDrDDfmfm}=";

        // Gabungkan heading dan pesan WXREV
        return "{$wxrevHeading}\n{$wxrevMessage}";
    }
}
