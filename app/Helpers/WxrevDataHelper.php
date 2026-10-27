<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class WxrevDataHelper
{
    private static $gp = [
        21 => 1,
        12 => 2,
        24 => 4,
        15 => 5,
        6 => 6,
        18 => 8,
        9 => 9
    ];

    private static $ar = 0;
    private static $ap = 0;
    private static $au = 2;
    private static $a = 0;

    public static function getGp($operatingHour)
    {
        return self::$gp[$operatingHour] ?? null;
    }

    public static function createMmyyGp(Carbon $date, $operatingHour)
    {
        $mmyy = $date->copy()->format('md');
        $gp = self::getGp($operatingHour);
        return $mmyy . $gp;
    }

    public static function createArTxTxTnTn(Collection $dataSinoptik, $dataSinoptikNextDay)
    {
        $tx = round($dataSinoptik->max('temp_max_c_txtxtx')); // Suhu maksimum dari 24 jam
        $tn = round($dataSinoptikNextDay->temp_min_c_tntntn); // Suhu minimum dari jam 00:00
        return self::$ar . sprintf('%02d%02d', $tx, $tn);
    }

    public static function createApPxPxPnPn(Collection $dataSinoptik)
    {
        $px = (int) round($dataSinoptik->max('pressure_qff_mb_derived'));
        $pn = (int) round($dataSinoptik->min('pressure_qff_mb_derived'));

        // **Hanya ambil 2 digit terakhir**
        return self::$ap . sprintf('%02d%02d', $px % 100, $pn % 100);
    }

    public static function createAUxUxUUn(Collection $dataSinoptik)
    {
        $ux = round($dataSinoptik->max('relative_humidity_pc')); // Kelembaban max
        $un = round($dataSinoptik->min('relative_humidity_pc')); // Kelembaban min
        return self::$au . sprintf('%02d%02d', $ux, $un);
    }

    public static function createArRRRR($dataSinoptikNextDay)
    {
        // Ambil data hujan dari jam 00:00 hari berikutnya
        $rainfall24h = $dataSinoptikNextDay->rainfall_24h_rrrr ?? 0;

        if (intval($rainfall24h) === 8888 || intval($rainfall24h) === 9999) {
            return self::$ar . '9999';
        }

        // **Format nilai hujan ke 4 digit**
        $rrrr = sprintf('%04d', $rainfall24h * 10);

        return self::$ar . $rrrr;
    }

    private static function roundHalfNearestOdd($value)
    {
        $floor = floor($value);
        $decimal = $value - $floor;

        if ($decimal < 0.5) {
            return $floor; // Dibulatkan ke bawah
        } elseif ($decimal > 0.5) {
            return ceil($value); // Dibulatkan ke atas
        } else { // Jika pecahan 0.5, bulatkan ke angka ganjil terdekat
            return ($floor % 2 == 0) ? $floor + 1 : $floor;
        }
    }

    private static function convertWindDirection($degree)
    {
        if ($degree == 0) return 0; // Calm
        if ($degree >= 25 && $degree <= 65) return 1;  // NE
        if ($degree >= 70 && $degree <= 110) return 2; // E
        if ($degree >= 115 && $degree <= 155) return 3; // SE
        if ($degree >= 160 && $degree <= 200) return 4; // S
        if ($degree >= 205 && $degree <= 245) return 5; // SW
        if ($degree >= 250 && $degree <= 295) return 6; // W
        if ($degree >= 300 && $degree <= 335) return 7; // NW
        if ($degree >= 340 || $degree <= 20) return 8;  // N
        return 9; // Variable
    }


    public static function createrDrDDfmfm(Collection $dataSinoptik, $operatingHour)
    {
        // Ambil semua data arah angin dalam 24 jam
        // 75 persen dianggap dominan
        $windRecords = $dataSinoptik->pluck('wind_dir_deg_dd')->toArray();

        // Konversi semua arah angin ke kategori yang sesuai
        $convertedWindRecords = array_map([self::class, 'convertWindDirection'], $windRecords);

        // Hitung frekuensi tiap kategori angin
        $windCounts = array_count_values($convertedWindRecords);
        arsort($windCounts); // Urutkan dari yang terbanyak

        $totalObservations = array_sum($windCounts); // Total semua pengamatan angin
        $accumulatedPercentage = 0; // Persentase akumulasi
        $selectedDirections = []; // Simpan arah angin yang dipilih
        $hasZero = false; // Apakah 0 termasuk dalam data?

        foreach ($windCounts as $direction => $count) {
            $percentage = ($count / $totalObservations) * 100;
            $rDrD = self::roundHalfNearestOdd($percentage);
            $accumulatedPercentage += $rDrD;
            $selectedDirections[] = ['direction' => $direction, 'percentage' => $rDrD];

            // Tandai jika ada 0 sebagai dominan
            if ($direction == 0) {
                $hasZero = true;
            }

            // **Jangan berhenti di 75% jika urutan pertama adalah 0**
            if ($accumulatedPercentage >= 75 && !($hasZero && count($selectedDirections) == 1)) {
                break;
            }
        }

        // Ambil kecepatan angin maksimum dalam 24 jam
        $ff = round($dataSinoptik->max('wind_speed_ff'));

        // **Format output WXREV**
        $wxrevParts = [];

        foreach ($selectedDirections as $index => $windData) {
            $rDrD = $windData['percentage'];
            $windCode = $windData['direction'];

            // Kalau dominan 0 lebih dari 75%, fmfm harus 00
            if ($windCode == 0 && $rDrD >= 75) {
                $wxrevParts[] = sprintf('%02d%d00', $rDrD, $windCode);
            } else {
                $wxrevParts[] = sprintf('%02d%d%02d', $rDrD, $windCode, $ff);
            }
        }

        return implode(' ', $wxrevParts);
    }
}
