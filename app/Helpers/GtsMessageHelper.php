<?php

namespace App\Helpers;

use Carbon\Carbon;

class GtsMessageHelper
{
    /**
     * Ekstrak jenis pesan cuaca dari teks.
     *
     * @param string $message
     * @return string|null
     */
    public static function extractTypeMessage(string $message): ?string
    {
        $types = [
            'METAR'    => '/METAR/',
            'SINOPTIK' => '/AAXX/',
            'DCH'      => '/DATA04/',
            'TAF'      => '/TAF/',
            'WXREV'    => '/WXREV/',
            'SPECI'    => '/SPECI/',
            'CLIMAT'   => '/CLIMAT/',
        ];

        foreach ($types as $type => $pattern) {
            if (preg_match($pattern, $message)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Ekstrak tanggal dari pesan cuaca.
     *
     * @param string $message
     * @return string|null
     */
    public static function extractDateFromMessage(string $message): ?string
    {
        if (preg_match('/(\d{6})(?=\s*[\n])/', $message, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Mengubah data ke format GTS yang sesuai dengan API v21 BMKG Satu
     * Sesuai spesifikasi payload endpoint /api/v21/gts/entry
     *
     * @param array $gtsMessageData
     * @return array
     */
    public static function mutateGTSMessageDataBeforeSent(array $gtsMessageData): array
    {
        $typeMessage = static::getTypeMessageValue($gtsMessageData['type_message'] ?? null);

        // 1. Ambil teks sandi
        $rawSandi = trim($gtsMessageData['sandi_gts'] ?? $gtsMessageData['sandi'] ?? '');

        // 2. Pastikan SELURUH pesan GTS (METAR, SPECI, DCH, SINOPTIK, TAF, dll) diakhiri karakter '='
        if (!str_ends_with($rawSandi, '=')) {
            $rawSandi .= '=';
        }

        // 3. Cek apakah sandi sudah memiliki WMO Header di baris pertama
        $hasHeader = preg_match('/^[A-Z]{4}\d{2}\s+[A-Z]{4}\s+\d{6}/', $rawSandi);

        if (!$hasHeader) {
            // Jika belum ada header, buat header WMO otomatis di baris atas
            $stationIcaoId = setting('station_icao_id') ?: 'WITN';
            $timestamp = $gtsMessageData['timestamp_data'] ?? now();

            $ttaaii = static::getHeading($typeMessage, $timestamp) ?: 'SAID40';
            $cccc   = strtoupper(trim($stationIcaoId));
            $ddhhmm = Carbon::parse($timestamp)->format('dHi');

            $headerLine = "{$ttaaii} {$cccc} {$ddhhmm}";
            $finalSandi = "{$headerLine}\n{$rawSandi}\n";
        } else {
            $finalSandi = "{$rawSandi}\n";
        }

        // 4. Return payload presisi sesuai ekspektasi API BMKG Satu
        return [
            'sandi'     => $finalSandi,
            'only_save' => false,
        ];
    }

    /**
     * Mengambil heading GTS berdasarkan tipe pesan dan waktu
     */
    protected static function getHeading($typeMessage, $timestamp)
    {
        $heading = setting('bmkgsatu_heading_list');

        // Parse JSON jika bernilai string
        if (is_string($heading)) {
            $heading = json_decode($heading, true);
        }

        if (empty($heading) || !is_array($heading)) {
            $heading = [
                'synop_1' => 'SMID01',
                'synop_2' => 'SMID02',
                'synop_3' => 'SMID03',
                'metar'   => 'SAID40',
                'speci'   => 'SPID40',
                'wxrev'   => 'WXID01',
                'dch'     => 'DATA04',
                'taf'     => 'FTID01',
                'climat'  => 'CSID01',
            ];
            logger("Peringatan: 'bmkgsatu_heading_list' kosong di database. Menggunakan default fallback.");
        }

        $hour = (int) Carbon::parse($timestamp)->format('H');

        if ($typeMessage == 1) {
            if (in_array($hour, [0, 6, 12, 18])) {
                return $heading['synop_1'] ?? 'SMID01';
            } elseif (in_array($hour, [3, 9, 15, 21])) {
                return $heading['synop_2'] ?? 'SMID02';
            } else {
                return $heading['synop_3'] ?? 'SMID03';
            }
        } elseif ($typeMessage == 4) {
            return $heading['metar'] ?? 'SAID40';
        } elseif ($typeMessage == 5) {
            return $heading['speci'] ?? 'SPID40';
        } elseif ($typeMessage == 8) {
            return $heading['wxrev'] ?? 'WXID01';
        } elseif ($typeMessage == 53) {
            return $heading['dch'] ?? 'DATA04';
        } elseif ($typeMessage == 6) {
            return $heading['taf'] ?? 'FTID01';
        } elseif ($typeMessage == 7) {
            return $heading['climat'] ?? 'CSID01';
        }

        return null;
    }

    public static function getCurrentTimestampIsoFormat()
    {
        return now()->format('Y-m-d\TH:i:s');
    }

    public static function convertTimestampToIsoFormat($timestamp)
    {
        return Carbon::parse($timestamp)->format('Y-m-d\TH:i:s');
    }

    public static function getTypeMessageValue($typeMessage): ?string
    {
        if (is_numeric($typeMessage)) {
            return (string) $typeMessage;
        }

        return match ($typeMessage) {
            'SINOPTIK' => "1",
            'METAR'    => "4",
            'SPECI'    => "5",
            'TAF'      => "6",
            'CLIMAT'   => "7",
            'WXREV'    => "8",
            'DCH'      => "53",
            default    => "0",
        };
    }

    public static function getTypeMessageString($typeMessageValue): ?string
    {
        return match ((string) $typeMessageValue) {
            "1"  => 'SINOPTIK',
            "4"  => 'METAR',
            "5"  => 'SPECI',
            "6"  => 'TAF',
            "7"  => 'CLIMAT',
            "8"  => 'WXREV',
            "53" => 'DCH',
            default => null,
        };
    }

    public static function getSentAndCreationSettingKey($typeMessageValue, $modeType): ?string
    {
        $typeMessageString = static::getTypeMessageString($typeMessageValue);

        if (!$typeMessageString) {
            return null;
        }

        switch ($modeType) {
            case 'sent_mode':
                $suffix = '_sent_mode';
                break;
            case 'creation_mode':
                $suffix = '_creation_mode';
                break;
            case 'sent_mode_offset':
                $suffix = '_sent_mode_offset';
                break;
            case 'creation_mode_offset':
                $suffix = '_creation_mode_offset';
                break;
            default:
                return null;
        }

        return 'bmkgsatu_' . strtolower($typeMessageString) . $suffix;
    }

    public static function generateGTSMessageId($typeMessage, $stationValue)
    {
        $now = now();
        $padZero = fn ($value, $length) => str_pad($value, $length, '0', STR_PAD_LEFT);

        return $padZero($typeMessage, 2) .
            $stationValue .
            $now->year .
            $padZero($now->month, 2) .
            $padZero($now->day, 2) .
            $padZero($now->hour, 2) .
            $padZero($now->minute, 2) .
            $padZero($now->second, 2);
    }
}
