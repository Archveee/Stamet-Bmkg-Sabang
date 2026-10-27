<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (! function_exists('setting')) {
    function setting($key = null, $default = null)
    {
        // Pastikan tabel 'settings' ada sebelum mencoba mengambil data
        if (!Schema::hasTable('settings')) {
            return $default;
        }

        // Ambil data dari cache atau query database jika belum ada
        $settings = Cache::remember('settings', 60, function () {
            return Setting::pluck('setting_value', 'setting_name')->toArray();
        });

        // Jika key berupa array, ambil hanya key yang diinginkan
        if (is_array($key)) {
            return collect($settings)->only($key)->toArray();
        }

        // Ambil nilai berdasarkan key
        $settingValue = $settings[$key] ?? $default;

        // Cek apakah nilai adalah JSON
        return is_json($settingValue) ? json_decode($settingValue, true) : $settingValue;
    }
}

if (! function_exists('is_json')) {
    function is_json($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
