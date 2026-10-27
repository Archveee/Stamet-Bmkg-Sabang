<?php

namespace App\Helpers;

class AwosDataHelper
{

    public static function getPresentWeatherNameString($present_weather_ww_code)
    {
        // Ambil array referensi yang sesuai
        $presentWeatherKeys = setting('reference_ww');

        // Balik key dan value
        $reversedPresentWeatherKeys = array_flip($presentWeatherKeys);

        // Cari string berdasarkan angka
        return $reversedPresentWeatherKeys[$present_weather_ww_code] ?? null;
    }


    public static function getSandiPetirHujan($present_weather_ww)
    {
        switch ($present_weather_ww) {
            case 'haze':
                return ' HZ';
            case 'inter sl ra':
                return ' -RA';
            case 'cns sl ra':
                return ' -RA';
            case 'inter mod ra':
                return ' RA';
            case 'cns mod ra':
                return ' RA';
            case 'inter heavy ra':
                return ' +RA';
            case 'cns heavy ra':
                return ' +RA';
            case 'sl/mod ts no ha+ra':
                return ' -TSRA';
            case 'sl/mod ts + ra':
                return ' TSRA';
            case 'heavy ts no ha+ra':
                return ' -TSRA';
            default:
                return '';
        }
    }

    public static function getSandiVisibility($visibility_vv)
    {
        if ((int)$visibility_vv < 10) {
            return $visibility_vv . '000';
        } elseif ((int)$visibility_vv >= 100) {
            return '0' . $visibility_vv;
        } elseif ((int)$visibility_vv == 0) {
            return '0';
        } else {
            return '9999';
        }
    }

    public static function getSandiCloudOktas($cloud_cover_oktas_m)
    {
        if ($cloud_cover_oktas_m == 0) {
            return 'CLEAR';
        } elseif (in_array($cloud_cover_oktas_m, [1, 2])) {
            return 'FEW';
        } elseif (in_array($cloud_cover_oktas_m, [3, 4, 5])) {
            return 'SCT';
        } elseif (in_array($cloud_cover_oktas_m, [6, 7])) {
            return 'BKN';
        } elseif ($cloud_cover_oktas_m >= 8) {
            return 'OVC';
        } else {
            return '';
        }
    }

    public static function getSandiCloud($cloud_layer_1_type_c, $cloud_layer_1_height_m_hshs, $cloud_layer_1_amt_oktas_ns, $cloud_layer_2_type_c = null, $cloud_layer_2_height_m_hshs = null, $cloud_layer_2_amt_oktas_ns = null)
    {
        $sandiCloud = "";

        if ($cloud_layer_1_type_c) {
            $h = (int)$cloud_layer_1_height_m_hshs / 30;
            $okta = (int)$cloud_layer_1_amt_oktas_ns;
            $code = self::getSandiCloudOktas($okta);
            $sandiCloud = sprintf("%s%03d", $code, $h);
        }

        if ($cloud_layer_2_type_c) {
            $h2 = (int)$cloud_layer_2_height_m_hshs / 30;
            $okta2 = (int)$cloud_layer_2_amt_oktas_ns;
            $code2 = self::getSandiCloudOktas($okta2);

            if (stripos(trim($cloud_layer_1_type_c), 'cb') !== false) {
                $sandiCloud .= "CB " . sprintf("%s%03d", $code2, $h2);
            } else {
                if ($cloud_layer_1_height_m_hshs == $cloud_layer_2_height_m_hshs) {
                    $okta += $okta2;
                    $code = self::getSandiCloudOktas($okta);
                    $sandiCloud = sprintf("%s%03d", $code, $h);
                } else {
                    $sandiCloud .= " " . sprintf("%s%03d", $code2, $h2);
                }
            }
        }

        return $sandiCloud;
    }

    
}


