<?php

namespace App\Services;

use App\Helpers\AwosDataHelper;
use App\Models\DataSinoptik;
use Carbon\Carbon;

class MetarComposer
{
    // minus verification
    
    public static function composeMetarMessage(DataSinoptik $dataSinoptik)
    {
        $headingTime = self::formatHeadingTime($dataSinoptik);
        $wind = self::formatWind($dataSinoptik);
        $petirHujan = AwosDataHelper::getSandiPetirHujan($dataSinoptik->present_weather_ww);
        $visibility = AwosDataHelper::getSandiVisibility($dataSinoptik->visibility_vv);
        $sandiCloud = AwosDataHelper::getSandiCloud(
            $dataSinoptik->cloud_layer_1_type_c,
            $dataSinoptik->cloud_layer_1_height_m_hshs,
            $dataSinoptik->cloud_layer_1_amt_oktas_ns,
            $dataSinoptik->cloud_layer_2_type_c,
            $dataSinoptik->cloud_layer_2_height_m_hshs,
            $dataSinoptik->cloud_layer_2_amt_oktas_ns
        );
        $temperature = self::roundValue($dataSinoptik->temp_drybulb_c_tttttt);
        $dewPoint = self::roundValue($dataSinoptik->temp_dewpoint_c_tdtdtd);
        $qff = round($dataSinoptik->pressure_qff_mb_derived);
        $nosig = " NOSIG";
        $stnId = setting('station_icao_id');
        $metarSaId = setting('bmkgsatu_heading_list')['metar'];

        // Header METAR
        $heading = $metarSaId . " " . $stnId . " " . $headingTime . "\n";

        // METAR Message
        $metarMessage = "METAR {$stnId} {$headingTime}Z {$wind}KT {$visibility}{$petirHujan} "
                      . "{$sandiCloud} {$temperature}/{$dewPoint} Q" . str_pad($qff, 4, "0", STR_PAD_LEFT) . "{$nosig}=";

        return $heading . $metarMessage;
    }

    protected static function formatHeadingTime(DataSinoptik $dataSinoptik)
    {
        return Carbon::parse($dataSinoptik->data_timestamp)->format('dHi');
    }

    protected static function formatWind(DataSinoptik $dataSinoptik)
    {
        return str_pad($dataSinoptik->wind_dir_deg_dd, 3, "0", STR_PAD_LEFT) .
               str_pad($dataSinoptik->wind_speed_ff, 2, "0", STR_PAD_LEFT);
    }

    protected static function roundValue($value)
    {
        return floor($value + 0.5);
    }
}
