<?php

namespace App\Services;

use App\Helpers\DchDataHelper;
use App\Models\DataSinoptik;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class DchComposer
{
    public static function composeDchMessage(Carbon $date)
    {
        // Ambil setting heading untuk DCH
        $dchHeadingCode = setting('bmkgsatu_heading_list')['dch'] ?? 'DATA04';
        $stationIcao = setting('station_icao_id');

        // Format waktu dalam UTC/GMT (misalnya "250000" untuk tanggal 25 jam 00:00)
        $headingTime = $date->copy()->addDay()->format('dHi');

        // Ambil data operasional dan kode stasiun
        $stationName = setting('station_name');
        $stnWmoId = setting('station_wmo_id');

        // Ambil data 24 jam
        $dataSinoptik = DataSinoptik::whereDate('data_timestamp', $date)
            ->select([
                'temp_drybulb_c_tttttt',
                'temp_max_c_txtxtx',
                'pressure_qff_mb_derived',
                'relative_humidity_pc',
                'wind_dir_deg_dd',
                'wind_speed_ff'
            ])
            ->get();

        // Ambil data jam 00:00 hari berikutnya
        $dataSinoptikNextDay = DataSinoptik::where('data_timestamp', $date->copy()->addDay()->startOfDay())
            ->select([
                'temp_min_c_tntntn',
                'rainfall_24h_rrrr',
                'sunshine_h_sss',
                'evaporation_24hours_mm_eee'
            ])
            ->first();

        if ($dataSinoptik->isEmpty() || !$dataSinoptikNextDay) {
            Notification::make()
                ->title('Data sinoptik tidak ditemukan')
                ->warning()
                ->send();
            return;
        }

        // Cek jika ada salah satu nilai di $dataSinoptikNextDay yang null atau kosong
        $values = [
            'temp_min_c_tntntn' => $dataSinoptikNextDay->temp_min_c_tntntn,
            'rainfall_24h_rrrr' => $dataSinoptikNextDay->rainfall_24h_rrrr,
            'sunshine_h_sss' => $dataSinoptikNextDay->sunshine_h_sss,
            'evaporation_24hours_mm_eee' => $dataSinoptikNextDay->evaporation_24hours_mm_eee
        ];

        // Filter nilai yang kosong/null
        $emptyFields = array_keys(array_filter($values, fn($value) => is_null($value) || $value === ''));

        if (!empty($emptyFields)) {
            Notification::make()
                ->title('Periksa Kembali Data')
                ->body('Terdapat data kosong di sinoptik jam 00: ' . implode(', ', $emptyFields))
                ->danger()
                ->duration(30000)
                ->send();
        }

        // Ambil data dari DchDataHelper
        $rr = DchDataHelper::getRR($dataSinoptikNextDay);
        $tt = DchDataHelper::getAvgT($dataSinoptik);
        $pp = DchDataHelper::getAvgP($dataSinoptik);
        $rh = DchDataHelper::getAvgU($dataSinoptik);
        $dd = DchDataHelper::getAvgDd($dataSinoptik);
        $ff = DchDataHelper::getAvgFf($dataSinoptik);
        $tx = DchDataHelper::getTx($dataSinoptik);
        $tn = DchDataHelper::getTn($dataSinoptikNextDay);
        $px = DchDataHelper::getPx($dataSinoptik);
        $pn = DchDataHelper::getPn($dataSinoptik);
        $rhx = DchDataHelper::getUx($dataSinoptik);
        $rhn = DchDataHelper::getUn($dataSinoptik);
        $ddx = DchDataHelper::getDdx($dataSinoptik);
        $ffx = DchDataHelper::getFfx($dataSinoptik);
        $eee = DchDataHelper::getEE($dataSinoptikNextDay);
        $sss = DchDataHelper::getSS($dataSinoptikNextDay);
        $ts = "0.0x"; // Edit di form saja

        // Susun heading DCH
        $dchHeading = "{$dchHeadingCode} {$stationIcao} {$headingTime}\n"
            . "DATA CUACA HARIAN TANGGAL " . strtoupper($date->isoFormat('D MMMM YYYY')) . "\n"
            . "{$stationName} {$stnWmoId}";

        // Jika kecepatan angin (FF) kurang dari 1, set DD/FF menjadi "CALM"
        $dd_ff = ($ff < 1) ? 'CALM' : sprintf('%s/%02d', $dd, $ff);
        $dd_ffx = ($ffx < 1) ? 'CALM' : sprintf('%d/%02d', $ddx, $ffx);

        // Susun pesan DCH dengan format yang sesuai
        $dchMessage = sprintf(
            "RR     = %.1f\n"
                . "TT     = %.1f\n"
                . "PP     = %.1f\n"
                . "RH     = %d\n"
                . "DD/FF  = %s\n"
                . "TX     = %.1f\n"
                . "TN     = %.1f\n"
                . "PX     = %.1f\n"
                . "PN     = %.1f\n"
                . "RHX    = %d\n"
                . "RHN    = %d\n"
                . "DD/FFX = %s\n"
                . "EEE    = %.1f\n"
                . "SSS    = %d%%\n"
                . "TS     = %s",
            $rr,
            $tt,
            $pp,
            $rh,
            $dd_ff,
            $tx,
            $tn,
            $px,
            $pn,
            $rhx,
            $rhn,
            $dd_ffx,
            $eee,
            $sss,
            $ts
        );

        // Gabungkan heading dan pesan DCH
        return "{$dchHeading}\n{$dchMessage}";
    }
}
