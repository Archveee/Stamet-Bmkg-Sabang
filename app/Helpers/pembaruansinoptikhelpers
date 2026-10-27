<?php

namespace App\Helpers;

use App\Models\DataAws;
use App\Models\DataAwos;
use App\Models\DataSinoptik;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class SinoptikDataHelper
{
    protected static function extractCloudFromEncodedSynop(string $encodedSynop)
    {
        // Pola regex untuk mencari kode cloud
        $clPattern = '/(8[\/\d]{4})(?:\s+333)/';

        if (preg_match($clPattern, $encodedSynop, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function calculateTempWetBulb(float $tempDryBulb, float $relativeHumidity, float $tempDewPoint): float
    {
        $result = 0;
        $tempWetBulb = $tempDewPoint;

        while ($result <= $relativeHumidity) {
            $esWet = 6.11 * pow(10, (7.5 * $tempWetBulb) / (237.3 + $tempWetBulb));
            $esDry = 6.11 * pow(10, (7.5 * $tempDryBulb) / (237.3 + $tempDryBulb));

            $result = (($esWet - (0.7947 * ($tempDryBulb - $tempWetBulb))) / $esDry) * 100;

            if ($result < $relativeHumidity) {
                $tempWetBulb += 0.1;
            }
        }

        return round($tempWetBulb, 1);
    }

    public static function normalizeWindData(?float $windSpeedMs, ?int $windDirAws, string $timestamp): array
    {
        // Default ambil dari AWS
        $windDir = $windDirAws;

        if ($windDir == -9999) {
            $awosData = DataAwos::where('data_timestamp', $timestamp)->first();
            if ($awosData && $awosData->wind_dir_10m !== null) {
                $windDir = $awosData->wind_dir_10m;
            } else {
                // Ambil timestamp satu menit sebelumnya
                $previousTimestamp = Carbon::parse($timestamp)->subMinute();

                $previousAwosData = DataAwos::where('data_timestamp', $previousTimestamp)->first();

                if ($previousAwosData && $previousAwosData->wind_dir_10m !== null) {
                    $windDir = $previousAwosData->wind_dir_10m;
                } else {
                    // Cara terakhir, ambil data angin dari data sinoptik terakhir
                    $previousSinoptikData = DataSinoptik::latest('data_timestamp')->first();
                    if ($previousSinoptikData && $previousSinoptikData->wind_dir_deg_dd !== null) {
                        $windDir = $previousSinoptikData->wind_dir_deg_dd;
                    }
                }
            }
        }

        // Konversi dan normalisasi
        $windSpeed = $windSpeedMs * 1.94384;
        if ($windSpeed < 1.0) {
            return [
                'wind_speed_ff' => 0,
                'wind_dir_deg_dd' => 0,
            ];
        }

        return [
            'wind_speed_ff' => (int) round($windSpeed),
            'wind_dir_deg_dd' => (int) round($windDir / 10) * 10,
        ];
    }

    public static function getClosestKeyName(string $inputName, array $keys)
    {
        $bestMatch = null;
        $highestScore = 0;

        foreach ($keys as $key => $value) {
            similar_text(strtolower($inputName), strtolower($key), $percent);

            if ($percent > 70 && $percent > $highestScore) {
                $highestScore = $percent;
                $bestMatch = $key;
            }
        }

        return $bestMatch;
    }

    public static function getBmkgsatuObserverId(string | int | null $observerId)
    {
        if (is_null($observerId)) {
            return null;
        }

        if (is_numeric($observerId)) {
            return (int) $observerId;
        }

        $observers = setting('bmkgsatu_observer_list');

        if (!$observers) {
            return null;
        }

        $observerKey = static::getClosestKeyName($observerId, $observers);

        return $observerKey ? (int) $observers[$observerKey] : null;
    }

    public static function getObserverNameFromId(int $observerId): ?string
    {
        $observers = setting('bmkgsatu_observer_list');

        if ($observers) {
            $observer = collect($observers)->firstWhere('observer_id', $observerId);

            if ($observer) {
                return $observer['observer_name'];
            }
        }

        return null;
    }

    public static function getWeatherCodeNumber($weatherNameString, $present = false)
    {
        if (is_numeric($weatherNameString)) {
            return $weatherNameString;
        }

        $weatherKeys = $present ? setting('reference_ww') : setting('reference_w1w2');

        if (!$weatherKeys) {
            return null;
        }

        $weatherCodeNumber = static::getClosestKeyName($weatherNameString, $weatherKeys);

        return $weatherCodeNumber;
    }

    public static function getWeatherNameString($weatherCodeNumber, $present = false)
    {
        $weatherKeys = $present ? setting('reference_ww') : setting('reference_w1w2');

        if ($weatherKeys) {
            $reversedWeatherKeys = array_flip($weatherKeys);

            return $reversedWeatherKeys[$weatherCodeNumber] ?? null;
        }

        return null;
    }

    protected static function createSinoptikDataTimestamp($dataTimestamp = null)
    {
        if (is_null($dataTimestamp)) {
            $dataTimestamp = now();
        } else {
            $dataTimestamp = Carbon::parse($dataTimestamp);
        }

        return $dataTimestamp->format('Y-m-d\TH');
    }

    public static function createSinoptikDataId($dataTimestamp = null)
    {
        $formattedTimestamp = static::createSinoptikDataTimestamp($dataTimestamp);

        return '1_sinoptik_' . $formattedTimestamp;
    }

    public static function sanitizeEncodedSynop(string $rawEncodedSynop)
    {
        $data = explode(' ', $rawEncodedSynop);

        $result = [];
        $i = 0;

        while ($i < count($data)) {
            $current = $data[$i];

            if (strlen($current) == 5) {
                $result[] = $current;
                $i++;
            }
            elseif (is_numeric($current)) {
                if (strlen($current) == 1) {
                    if ($i + 1 < count($data) && strlen($data[$i + 1]) == 4) {
                        $result[] = $current . $data[$i + 1];
                        $i += 2;
                    } else {
                        $i++;
                    }
                }
                elseif (strlen($current) == 2) {
                    if ($i + 1 < count($data) && strlen($data[$i + 1]) == 3) {
                        $result[] = $current . $data[$i + 1];
                        $i += 2;
                    } else {
                        $i++;
                    }
                } else {
                    $result[] = $current;
                    $i++;
                }
            } else {
                $result[] = $current;
                $i++;
            }
        }

        $resultStr = implode(' ', $result);

        $resultStr = str_replace(' Z ', " \n", $resultStr);
        $resultStr = preg_replace('/(?<!\n) 333 /', " \n  333 ", $resultStr);
        $resultStr = preg_replace('/(\n)(\s*)(\n)/', '$1$2', $resultStr);

        if (substr(trim($resultStr), -1) !== '=') {
            $resultStr = rtrim($resultStr) . '=';
        }

        return $resultStr;
    }

    protected static function getSinoptikHeader($dataTimestamp)
    {
        $headingList = setting('bmkgsatu_heading_list');
        if (is_null($headingList)) {
            throw new \Exception('Data heading tidak ditemukan di pengaturan');
        }

        $heading = '';
        $timestamp = Carbon::parse($dataTimestamp);
        $hour = $timestamp->hour;

        if (in_array($hour, [0, 6, 12, 18])) {
            $heading = $headingList['synop_1'];
        } elseif (in_array($hour, [3, 9, 15, 21])) {
            $heading = $headingList['synop_2'];
        } else {
            $heading = $headingList['synop_3'];
        }

        $header = $heading . ' ' . setting('station_icao_id') . ' ' . $timestamp->format('dH') . '00' . "\n";
        return $header;
    }

    public static function createEncodedSynopWithHeader(string $rawEncodedSynop, $dataTimestamp)
    {
        $encodedSynop = static::sanitizeEncodedSynop($rawEncodedSynop);
        $header = static::getSinoptikHeader($dataTimestamp);
        return $header . $encodedSynop;
    }

    public static function mutateSinoptikDataBeforeSave(array $sinoptikData)
    {
        $sinoptikData['present_weather_ww'] = (int) static::getWeatherCodeNumber($sinoptikData['present_weather_ww'], true);
        $sinoptikData['past_weather_w1'] = (int) static::getWeatherCodeNumber($sinoptikData['past_weather_w1']);
        $sinoptikData['past_weather_w2'] = (int) static::getWeatherCodeNumber($sinoptikData['past_weather_w2']);

        $cloudCode = static::extractCloudFromEncodedSynop($sinoptikData['encoded_synop']);
        $sinoptikData['cloud_low_type_cl'] = $cloudCode ? substr($cloudCode, 2, 1) : null;
        $sinoptikData['cloud_med_type_cm'] = $cloudCode ? substr($cloudCode, 3, 1) : null;
        $sinoptikData['cloud_high_type_ch'] = $cloudCode ? substr($cloudCode, 4, 1) : null;
        $sinoptikData['cloud_low_cover_oktas'] = isset($sinoptikData['cloud_low_cover_oktas']) ? (string) $sinoptikData['cloud_low_cover_oktas'] : null;
        $sinoptikData['cloud_med_cover_oktas'] = isset($sinoptikData['cloud_med_cover_oktas']) ? (string) $sinoptikData['cloud_med_cover_oktas'] : null;
        $sinoptikData['cloud_high_cover_oktas'] = isset($sinoptikData['cloud_high_cover_oktas']) ? (string) $sinoptikData['cloud_high_cover_oktas'] : null;
        $sinoptikData['cloud_cover_oktas_m'] = isset($sinoptikData['cloud_cover_oktas_m']) ? (string) $sinoptikData['cloud_cover_oktas_m'] : null;
        $sinoptikData['cloud_layer_1_amt_oktas_ns'] = isset($sinoptikData['cloud_layer_1_amt_oktas_ns']) ? (int) $sinoptikData['cloud_layer_1_amt_oktas_ns'] : null;
        $sinoptikData['cloud_layer_2_amt_oktas_ns'] = isset($sinoptikData['cloud_layer_2_amt_oktas_ns']) ? (int) $sinoptikData['cloud_layer_2_amt_oktas_ns'] : null;
        $sinoptikData['cloud_layer_3_amt_oktas_ns'] = isset($sinoptikData['cloud_layer_3_amt_oktas_ns']) ? (int) $sinoptikData['cloud_layer_3_amt_oktas_ns'] : null;

        $sinoptikData['cloud_low_type_1'] = (string) ($sinoptikData['cloud_low_type_1'] ?? $sinoptikData['cloud_low_type_cl'] ?? null);
        $sinoptikData['cloud_med_type_1'] = (string) ($sinoptikData['cloud_med_type_1'] ?? $sinoptikData['cloud_med_type_cm'] ?? null);
        $sinoptikData['cloud_high_type_1'] = (string) ($sinoptikData['cloud_high_type_1'] ?? $sinoptikData['cloud_high_type_ch'] ?? null);
        $sinoptikData['cloud_low_cover_1'] = (string) ($sinoptikData['cloud_low_cover_1'] ?? $sinoptikData['cloud_low_cover_oktas'] ?? null);
        $sinoptikData['cloud_med_cover_1'] = (string) ($sinoptikData['cloud_med_cover_1'] ?? $sinoptikData['cloud_med_cover_oktas'] ?? null);
        $sinoptikData['cloud_high_cover_1'] = (string) ($sinoptikData['cloud_high_cover_1'] ?? $sinoptikData['cloud_high_cover_oktas'] ?? null);

        $sinoptikData['observer_id'] = static::getBmkgsatuObserverId($sinoptikData['observer_id'] ?? null);

        $sinoptikData['encoded_synop'] = static::sanitizeEncodedSynop($sinoptikData['encoded_synop']);
        $sinoptikData['data_timestamp'] = static::createSinoptikDataTimestamp($sinoptikData['data_timestamp']);

        return $sinoptikData;
    }

    /**
     * Menambahi/mengubah data sinoptik untuk dikirim ke BMKG Satu v21
     * Disesuaikan 100% dengan skema JSON resmi API BMKG Satu v21
     *
     * @param array $sinoptikData
     * @return array
     */
    public static function mutateSinoptikDataBeforeSent(array $sinoptikData): array
    {
        // Format Timestamp ISO 8601 UTC/Zulu (Contoh: 2026-07-27T02:00:00Z)
        $timestampFormat = Carbon::parse($sinoptikData['data_timestamp'])
            ->setTimezone('UTC')
            ->format('Y-m-d\TH:i:s\Z');

        // Helpers untuk tipe data presisi sesuai server BMKG Satu v21
        $toStrOrEmpty = fn($val) => ($val !== null && $val !== '') ? (string) $val : "";
        $toInt        = fn($val) => ($val !== null && $val !== '') ? (int) $val : null;
        $toFloat      = fn($val) => ($val !== null && $val !== '') ? (float) $val : null;

        $bmkgsoftId = setting('bmkgsoft_id') ?? setting('station_bmkgsoft_id') ?? 1;

        // Validasi jenis awan rendah untuk kolom 1 (kiri) dan kolom 2 (kanan)
        $type1 = (string) ($sinoptikData['cloud_low_type_1'] ?? $sinoptikData['cloud_low_type_cl'] ?? '');
        $type2 = (string) ($sinoptikData['cloud_low_type_2'] ?? '');

        // Sudut elevasi (ec) dan Arah sebenarnya (da) hanya berlaku jika jenis awannya Cu (8) atau Cb (9)
        $isCuOrCb1 = in_array($type1, ['8', '9'], true);
        $isCuOrCb2 = in_array($type2, ['8', '9'], true);

        return [
            'data_timestamp'                      => $timestampFormat,
            'bmkgsoft_id'                         => (int) $bmkgsoftId,
            'observer_id'                         => $toInt(static::getBmkgsatuObserverId($sinoptikData['observer_id'] ?? null)),
            'wind_dir_deg_dd'                     => $toInt($sinoptikData['wind_dir_deg_dd'] ?? null),
            'wind_indicator_iw'                   => $toInt($sinoptikData['wind_indicator_iw'] ?? 4),
            'wind_speed_ff'                       => $toInt($sinoptikData['wind_speed_ff'] ?? null),
            'visibility_vv'                       => $toInt($sinoptikData['visibility_vv'] ?? null),

            // Field Cuaca Lalu Wajib String ("0", "1", dst.)
            'past_weather_w1'                     => $toStrOrEmpty($sinoptikData['past_weather_w1'] ?? "0"),
            'past_weather_w2'                     => $toStrOrEmpty($sinoptikData['past_weather_w2'] ?? "0"),
            'present_weather_ww'                  => $toInt($sinoptikData['present_weather_ww'] ?? null),

            'pressure_reading_mb'                 => $toFloat($sinoptikData['pressure_reading_mb'] ?? null),
            'pressure_temp_c'                     => $toFloat($sinoptikData['pressure_temp_c'] ?? null),
            'rainfall_last_mm'                    => $toFloat($sinoptikData['rainfall_last_mm'] ?? null),
            'temp_drybulb_c_tttttt'               => $toFloat($sinoptikData['temp_drybulb_c_tttttt'] ?? null),
            'temp_max_c_txtxtx'                   => $toFloat($sinoptikData['temp_max_c_txtxtx'] ?? null),
            'temp_min_c_tntntn'                   => $toFloat($sinoptikData['temp_min_c_tntntn'] ?? null),
            'temp_wetbulb_c'                      => $toFloat($sinoptikData['temp_wetbulb_c'] ?? null),

            // Field Awan Utama Wajib String ("1", "2", dst.)
            'cloud_cover_oktas_m'                 => $toStrOrEmpty($sinoptikData['cloud_cover_oktas_m'] ?? null),
            'cloud_low_type_cl'                   => $toStrOrEmpty($sinoptikData['cloud_low_type_cl'] ?? null),
            'cloud_med_type_cm'                   => $toStrOrEmpty($sinoptikData['cloud_med_type_cm'] ?? null),
            'cloud_high_type_ch'                  => $toStrOrEmpty($sinoptikData['cloud_high_type_ch'] ?? null),
            'cloud_low_cover_oktas'               => $toStrOrEmpty($sinoptikData['cloud_low_cover_oktas'] ?? null),
            'cloud_med_cover_oktas'               => $toStrOrEmpty($sinoptikData['cloud_med_cover_oktas'] ?? null),
            'cloud_high_cover_oktas'              => $toStrOrEmpty($sinoptikData['cloud_high_cover_oktas'] ?? null),

            // Rincian Jenis Awan (Tipe string, jika kosong dikirim "")
            'cloud_low_type_1'                    => $toStrOrEmpty($sinoptikData['cloud_low_type_1'] ?? null),
            'cloud_low_type_2'                    => $toStrOrEmpty($sinoptikData['cloud_low_type_2'] ?? null),
            'cloud_low_type_3'                    => $toStrOrEmpty($sinoptikData['cloud_low_type_3'] ?? null),
            'cloud_med_type_1'                    => $toStrOrEmpty($sinoptikData['cloud_med_type_1'] ?? null),
            'cloud_med_type_2'                    => $toStrOrEmpty($sinoptikData['cloud_med_type_2'] ?? null),
            'cloud_high_type_1'                   => $toStrOrEmpty($sinoptikData['cloud_high_type_1'] ?? null),
            'cloud_high_type_2'                   => $toStrOrEmpty($sinoptikData['cloud_high_type_2'] ?? null),

            // Tinggi Dasar Awan (Integer / null)
            'cloud_low_base_1'                    => $toInt($sinoptikData['cloud_low_base_1'] ?? null),
            'cloud_low_base_2'                    => $toInt($sinoptikData['cloud_low_base_2'] ?? null),
            'cloud_low_base_3'                    => $toInt($sinoptikData['cloud_low_base_3'] ?? null),
            'cloud_med_base_1'                    => $toInt($sinoptikData['cloud_med_base_1'] ?? null),
            'cloud_med_base_2'                    => $toInt($sinoptikData['cloud_med_base_2'] ?? null),
            'cloud_high_base_1'                   => $toInt($sinoptikData['cloud_high_base_1'] ?? null),
            'cloud_high_base_2'                   => $toInt($sinoptikData['cloud_high_base_2'] ?? null),

            'cloud_low_peak_1'                    => $toInt($sinoptikData['cloud_low_peak_1'] ?? null),
            'cloud_low_peak_2'                    => $toInt($sinoptikData['cloud_low_peak_2'] ?? null),

            // Jumlah Awan Rincian (String, jika kosong dikirim "")
            'cloud_low_cover_1'                   => $toStrOrEmpty($sinoptikData['cloud_low_cover_1'] ?? null),
            'cloud_low_cover_2'                   => $toStrOrEmpty($sinoptikData['cloud_low_cover_2'] ?? null),
            'cloud_low_cover_3'                   => $toStrOrEmpty($sinoptikData['cloud_low_cover_3'] ?? null),
            'cloud_med_cover_1'                   => $toStrOrEmpty($sinoptikData['cloud_med_cover_1'] ?? null),
            'cloud_med_cover_2'                   => $toStrOrEmpty($sinoptikData['cloud_med_cover_2'] ?? null),
            'cloud_high_cover_1'                  => $toStrOrEmpty($sinoptikData['cloud_high_cover_1'] ?? null),
            'cloud_high_cover_2'                  => $toStrOrEmpty($sinoptikData['cloud_high_cover_2'] ?? null),

            // Arah Awan (Integer / null)
            'cloud_low_dir_1'                     => $toInt($sinoptikData['cloud_low_dir_1'] ?? null),
            'cloud_low_dir_2'                     => $toInt($sinoptikData['cloud_low_dir_2'] ?? null),
            'cloud_low_dir_3'                     => $toInt($sinoptikData['cloud_low_dir_3'] ?? null),
            'cloud_med_dir_1'                     => $toInt($sinoptikData['cloud_med_dir_1'] ?? null),
            'cloud_med_dir_2'                     => $toInt($sinoptikData['cloud_med_dir_2'] ?? null),
            'cloud_high_dir_1'                    => $toInt($sinoptikData['cloud_high_dir_1'] ?? null),
            'cloud_high_dir_2'                    => $toInt($sinoptikData['cloud_high_dir_2'] ?? null),

            // Sudut Elevasi dan Arah Sebenarnya (Hanya terisi jika awan Cu/Cb pada kolom masing-masing)
            'cloud_elevation_angle_ec_1'          => $isCuOrCb1 ? $toInt($sinoptikData['cloud_elevation_angle_ec_1'] ?? null) : null,
            'cloud_elevation_angle_ec_2'          => $isCuOrCb2 ? $toInt($sinoptikData['cloud_elevation_angle_ec_2'] ?? null) : null,
            'cloud_low_dir_true_da_1'             => $isCuOrCb1 ? $toInt($sinoptikData['cloud_low_dir_true_da_1'] ?? null) : null,
            'cloud_low_dir_true_da_2'             => $isCuOrCb2 ? $toInt($sinoptikData['cloud_low_dir_true_da_2'] ?? null) : null,
            'cloud_vertical_vis'                  => $toInt($sinoptikData['cloud_vertical_vis'] ?? null),

            'solar_rad_24h_jcm2_f24'              => $toFloat($sinoptikData['solar_rad_24h_jcm2_f24'] ?? null),
            'sunshine_h_sss'                      => $toFloat($sinoptikData['sunshine_h_sss'] ?? null),
            'evaporation_24hours_mm_eee'          => $toFloat($sinoptikData['evaporation_24hours_mm_eee'] ?? null),
            'evaporation_eq_indicator_ie'         => $toInt($sinoptikData['evaporation_eq_indicator_ie'] ?? null),

            'land_cond'                           => $toInt($sinoptikData['land_cond'] ?? 0),
            'land_note'                           => $toStrOrEmpty($sinoptikData['land_note'] ?? null),

            'pressure_qff_mb_derived'             => $toFloat($sinoptikData['pressure_qff_mb_derived'] ?? null),
            'pressure_qfe_mb_derived'             => $toFloat($sinoptikData['pressure_qfe_mb_derived'] ?? null),
        ];
    }

    public static function fetchDataSinoptik(string $timestamp, ?string $userId = null, bool $selfData = False)
    {
        $sinoptikServerUrl = setting('excel_server_url') . '/sinoptik/fetch';

        try {
            $params = ['timestamp' => $timestamp];
            if ($userId !== null) {
                $params['user_id'] = $userId;
            }
            $response = Http::withToken(setting('excel_server_token'))
                ->get($sinoptikServerUrl, $params);

            return $response->json();
        } catch (RequestException $e) {
            throw new \Exception("Terjadi kesalahan saat mengambil data sinoptik {$timestamp}");
        }
    }

    public static function sendDataAwsToServerSinoptik(string $timestamp, int $minute = 50, ?string $userId = null)
    {
        try {
            $targetTime = Carbon::parse($timestamp)->setMinutes($minute)->setSeconds(0);
            $dataTimestamp = $targetTime->copy()->format('Y-m-d H:00:00');
            $hour = Carbon::parse($dataTimestamp)->hour;

            $referenceTime = $targetTime->copy()->subHour();
            $dataAws = DataAws::where('data_timestamp', $referenceTime->format('Y-m-d H:i:s'))->first();

            if (!$dataAws) {
                logger("Data AWS menit ke-{$minute} tidak ditemukan");
                return ['status' => 'error', 'detail' => "Data AWS menit ke-{$minute} tidak ditemukan."];
            }

            $tempWetBulb = static::calculateTempWetBulb($dataAws->temp_drybulb_c_tttttt, $dataAws->relative_humidity_pc, $dataAws->temp_dewpoint_c_tdtdtd);

            $wind = static::normalizeWindData(
                $dataAws->wind_speed_ff,
                $dataAws->wind_dir_deg_dd,
                $referenceTime->format('Y-m-d H:i:s')
            );

            $windSpeed = $wind['wind_speed_ff'];
            $windDir = $wind['wind_dir_deg_dd'];

            $payload = [
                'data_timestamp' => $dataTimestamp,
                'wind_dir_deg_dd' => $windDir,
                'wind_speed_ff' => $windSpeed,
                'pressure_qfe_mb_derived' => $dataAws->pressure_qfe_mb_derived,
                'pressure_qff_mb_derived' => $dataAws->pressure_qff_mb_derived,
                'temp_drybulb_c_tttttt' => $dataAws->temp_drybulb_c_tttttt,
                'temp_dewpoint_c_tdtdtd' => $dataAws->temp_dewpoint_c_tdtdtd,
                'temp_wetbulb_c' => $tempWetBulb,
                'temp_max_c_txtxtx' => $dataAws->temp_max_c_txtxtx,
                'temp_min_c_tntntn' => $dataAws->temp_min_c_tntntn,
                'relative_humidity_pc' => $dataAws->relative_humidity_pc,
                'created_by' => $userId ?? null,
            ];

            if ($hour % 3 === 0) {
                $rain3Start = $targetTime->copy()->subHours(3)->addMinute();
                $rain3End = $targetTime;

                $rain3 = DataAws::whereBetween('data_timestamp', [$rain3Start, $rain3End])
                    ->sum('rainfall_last_mm');

                $payload['rainfall_last_mm'] = round($rain3, 1);
            }

            $headers = [
                'Authorization' => 'Bearer ' . setting('excel_server_token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            $sinoptikServerUrl = setting('excel_server_url');
            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->post($sinoptikServerUrl . '/sinoptik/input', $payload);

            if ($response->successful()) {
                logger("Data AWS {$dataTimestamp} berhasil dikirim ke server sinoptik.");
                return ['status' => 'success'];
            } else {
                logger("Gagal mengirim data ke server sinoptik: {$response->status()} - {$response->body()}");
                return ['status' => 'error', 'detail' => "Gagal mengirim data: {$response->body()}"];
            }
        } catch (\Throwable $e) {
            logger("Error saat mengirim data ke server sinoptik: " . $e->getMessage());
            return ['status' => 'error', 'detail' => $e->getMessage()];
        }
    }

    public static function restartExcelSinoptik(): array
    {
        $url = setting('excel_server_url') . '/sinoptik/restart-excel';
        $token = setting('excel_server_token');

        try {
            $response = Http::withToken($token)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'detail' => 'Program excel sinoptik berhasil direstart.'
                ];
            } else {
                return [
                    'status' => 'error',
                    'detail' => 'Gagal merestart excel sinoptik.'
                ];
            }
        } catch (\Throwable $e) {
            logger('Gagal merestart excel: ' . $e->getMessage());

            return [
                'status' => 'error',
                'detail' => 'Gagal merestart excel sinoptik.',
            ];
        }
    }
}