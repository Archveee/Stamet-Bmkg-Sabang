<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class DchDataHelper
{


    public static function getAvgT(Collection $dataSinoptik)
    {
        $t = round($dataSinoptik->average('temp_drybulb_c_tttttt'), 1);
        return $t;
    }

    public static function getTx(Collection $dataSinoptik)
    {
        $tx = $dataSinoptik->max('temp_max_c_txtxtx'); // Suhu maksimum dari 24 jam
        return $tx;
    }

    public static function getTn($dataSinoptikNextDay)
    {
        $tn = $dataSinoptikNextDay->temp_min_c_tntntn; // Suhu minimum dari jam 00:00
        return $tn;
    }

    public static function getAvgP(Collection $dataSinoptik)
    {
        $p = round($dataSinoptik->average('pressure_qff_mb_derived'), 1);
        return $p;
    }

    public static function getPx(Collection $dataSinoptik)
    {
        $px = $dataSinoptik->max('pressure_qff_mb_derived');
        return $px;
    }

    public static function getPn(Collection $dataSinoptik)
    {
        $pn = $dataSinoptik->min('pressure_qff_mb_derived');
        return $pn;
    }

    public static function getAvgU(Collection $dataSinoptik)
    {
        $u = round($dataSinoptik->average('relative_humidity_pc'), 1);
        return $u;
    }

    public static function getUx(Collection $dataSinoptik)
    {
        $ux = $dataSinoptik->max('relative_humidity_pc'); // Kelembaban max
        return $ux;
    }

    public static function getUn(Collection $dataSinoptik)
    {
        $un = $dataSinoptik->min('relative_humidity_pc'); // Kelembaban min
        return $un;
    }

    public static function getAvgDd(Collection $dataSinoptik)
    {
        $dd = round($dataSinoptik->average('wind_dir_deg_dd'));
        # convert to wind letter, E W or else
        $ddCode = static::convertWindDirection($dd);
        return $ddCode;
    }

    public static function getAvgFf(Collection $dataSinoptik)
    {
        $ff = round($dataSinoptik->average('wind_speed_ff'), 1);
        return $ff;
    }

    public static function getFfx(Collection $dataSinoptik)
    {
        $ff = $dataSinoptik->max('wind_speed_ff');
        return $ff;
    }

    public static function getDdx(Collection $dataSinoptik)
    {
        // Ambil baris data dengan kecepatan angin maksimum
        $ffxRecord = $dataSinoptik->where('wind_speed_ff', self::getFfx($dataSinoptik))->first();

        // Jika ada, ambil arah angin (`wind_dir_deg_dd`), jika tidak ada, kembalikan null
        return $ffxRecord ? $ffxRecord->wind_dir_deg_dd : null;
    }


    public static function getRR($dataSinoptikNextDay)
    {
        // Ambil data hujan dari jam 00:00 hari berikutnya
        $rr = $dataSinoptikNextDay->rainfall_24h_rrrr ?? 0.0;

        if (intval($rr) === 8888) {
            return 8888;
        }

        if (intval($rr) === 9999) {
            return 9999;
        }

        return $rr;
    }


    public static function getEE($dataSinoptikNextDay)
    {
        // Ambil data evaporation dari jam 00:00 hari berikutnya
        $ee = $dataSinoptikNextDay->evaporation_24hours_mm_eee ?? 0.0;

        return $ee;
    }

    public static function getSS($dataSinoptikNextDay)
    {
        // Ambil data lama penyinaran dari jam 00:00 hari berikutnya
        $ss = $dataSinoptikNextDay->sunshine_h_sss ?? 0.0;

        # convert to percent
        $percentSs = round($ss / 8 * 100, 1);

        if ($percentSs > 100){
            $percentSs = 100;
        }

        return $percentSs;
    }

    private static function convertWindDirection($degree)
    {
        if ($degree == 0) return 'CALM'; // Calm
        if ($degree >= 25 && $degree <= 65) return 'NE';
        if ($degree >= 70 && $degree <= 110) return 'E';
        if ($degree >= 115 && $degree <= 155) return 'SE';
        if ($degree >= 160 && $degree <= 200) return 'S';
        if ($degree >= 205 && $degree <= 245) return 'SW';
        if ($degree >= 250 && $degree <= 295) return 'W';
        if ($degree >= 300 && $degree <= 335) return 'NW';
        if ($degree >= 340 || $degree <= 20) return 'N';
        return ''; # null
    }
}
