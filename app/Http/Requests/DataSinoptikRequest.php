<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataSinoptikRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data_timestamp' => 'nullable|date',
            'cloud_cover_oktas_m' => 'nullable|numeric|max:255',
            'cloud_low_type_cl' => 'nullable|string|max:255',
            'cloud_med_type_cm' => 'nullable|string|max:255',
            'cloud_high_type_ch' => 'nullable|string|max:255',
            'cloud_low_cover_oktas' => 'nullable|numeric|max:8',
            'cloud_med_cover_oktas' => 'nullable|numeric|max:8',
            'cloud_high_cover_oktas' => 'nullable|numeric|max:8',
            'cloud_layer_1_type_c' => 'nullable|numeric',
            'cloud_layer_1_height_m_hshs' => 'nullable|integer',
            'cloud_layer_1_amt_oktas_ns' => 'nullable|numeric|max:8',
            'cloud_layer_2_type_c' => 'nullable|numeric',
            'cloud_layer_2_height_m_hshs' => 'nullable|integer',
            'cloud_layer_2_amt_oktas_ns' => 'nullable|numeric|max:8',
            'cloud_layer_3_type_c' => 'nullable|numeric',
            'cloud_layer_3_height_m_hshs' => 'nullable|integer',
            'cloud_layer_3_amt_oktas_ns' => 'nullable|numeric|max:8',
            'cloud_layer_4_type_c' => 'nullable|numeric',
            'cloud_layer_4_height_m_hshs' => 'nullable|integer',
            'cloud_layer_4_amt_oktas_ns' => 'nullable|numeric|max:8',
            'evaporation_24hours_mm_eee' => 'nullable|numeric',
            'evaporation_eq_indicator_ie' => 'nullable|integer',
            'land_cond' => 'nullable|integer',
            'land_note' => 'nullable|string|max:255',
            'past_weather_w1' => 'nullable|numeric|max:9',
            'past_weather_w2' => 'nullable|numeric|max:9',
            'present_weather_ww' => 'nullable|numeric|max:97',
            'pressure_reading_mb' => 'nullable|numeric',
            'pressure_temp_c' => 'nullable|numeric',
            'rainfall_24h_rrrr' => 'nullable|numeric',
            'rainfall_6h_rrr' => 'nullable|numeric',
            'rainfall_indicator_ir' => 'nullable|integer',
            'rainfall_last_mm' => 'nullable|numeric',
            'solar_rad_24h_jcm2_f24' => 'nullable|numeric',
            'sunshine_h_sss' => 'nullable|numeric',
            'temp_dewpoint_c_tdtdtd' => 'nullable|numeric',
            'temp_drybulb_c_tttttt' => 'nullable|numeric',
            'temp_max_c_txtxtx' => 'nullable|numeric',
            'temp_min_c_tntntn' => 'nullable|numeric',
            'temp_wetbulb_c' => 'nullable|numeric',
            'visibility_vv' => 'nullable|integer',
            'weather_indicator_ix' => 'nullable|integer',
            'wind_dir_deg_dd' => 'nullable|integer',
            'wind_indicator_iw' => 'nullable|integer',
            'wind_speed_ff' => 'nullable|numeric',
            'relative_humidity_pc' => 'nullable|integer',
            'pressure_qff_mb_derived' => 'nullable|numeric',
            'pressure_qfe_mb_derived' => 'nullable|numeric',
            'pressure_tend_3h_mb_ppp' => 'nullable|numeric',
            'pressure_tend_24h_mb_ppp' => 'nullable|numeric',
            'cloud_low_type_1' => 'nullable|string|max:255',
            'cloud_low_type_2' => 'nullable|string|max:255',
            'cloud_low_type_3' => 'nullable|string|max:255',
            'cloud_med_type_1' => 'nullable|string|max:255',
            'cloud_med_type_2' => 'nullable|string|max:255',
            'cloud_high_type_1' => 'nullable|string|max:255',
            'cloud_high_type_2' => 'nullable|string|max:255',
            'cloud_low_base_1' => 'nullable|integer',
            'cloud_low_base_2' => 'nullable|integer',
            'cloud_low_base_3' => 'nullable|integer',
            'cloud_med_base_1' => 'nullable|integer',
            'cloud_med_base_2' => 'nullable|integer',
            'cloud_high_base_1' => 'nullable|integer',
            'cloud_high_base_2' => 'nullable|integer',
            'cloud_low_peak_1' => 'nullable|integer',
            'cloud_low_peak_2' => 'nullable|integer',
            'cloud_low_cover_1' => 'nullable|numeric',
            'cloud_low_cover_2' => 'nullable|numeric',
            'cloud_low_cover_3' => 'nullable|numeric',
            'cloud_med_cover_1' => 'nullable|numeric',
            'cloud_med_cover_2' => 'nullable|numeric',
            'cloud_high_cover_1' => 'nullable|numeric',
            'cloud_high_cover_2' => 'nullable|numeric',
            'cloud_low_dir_1' => 'nullable|integer|max:9',
            'cloud_low_dir_2' => 'nullable|integer|max:9',
            'cloud_low_dir_3' => 'nullable|integer|max:9',
            'cloud_med_dir_1' => 'nullable|integer|max:9',
            'cloud_med_dir_2' => 'nullable|integer|max:9',
            'cloud_high_dir_1' => 'nullable|integer|max:9',
            'cloud_high_dir_2' => 'nullable|integer|max:9',
            'cloud_elevation_angle_ec_1' => 'nullable|integer',
            'cloud_elevation_angle_ec_2' => 'nullable|integer',
            'cloud_low_dir_true_da_1' => 'nullable|integer',
            'cloud_low_dir_true_da_2' => 'nullable|integer',
            'cloud_vertical_vis' => 'nullable|integer',
            'encoded_synop' => 'nullable|string',
            'encoded_synop_with_header' => 'nullable|string',
            'observer_id' => 'nullable|integer',
            'koreksi_pressure_qff_mb_derived' => 'nullable|numeric',
            'koreksi_pressure_qfe_mb_derived' => 'nullable|numeric'
        ];
        
    }
}
