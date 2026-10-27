<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataAwosRequest extends FormRequest
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
            'device_id' => 'nullable|string|max:100',
            'data_timestamp' => 'nullable|date',
            'wind_spd_2m_kt' => 'nullable|numeric',
            'wind_dir_2m' => 'nullable|integer',
            'wind_gustav_10m_kt' => 'nullable|numeric',
            'wind_spdx_2m_kt' => 'nullable|numeric',
            'wind_spd_10m_kt' => 'nullable|numeric',
            'wind_dir_10m' => 'nullable|integer',
            'wind_dirccw_10m' => 'nullable|integer',
            'wind_dircw_10m' => 'nullable|integer',
            'wind_peak_10m_kt' => 'nullable|numeric',
            'at_5m_c' => 'nullable|numeric',
            'dp_5m_c' => 'nullable|numeric',
            'rh_5m' => 'nullable|numeric',
            'sr_6m_j_mncm2' => 'nullable|numeric',
            'qnh_1m_mb' => 'nullable|numeric',
            'qfe_1m_mb' => 'nullable|numeric',
            'bplow_1m_mb' => 'nullable|numeric',
            'pp_1h_mm' => 'nullable|numeric',
            'pp_6h_mm' => 'nullable|numeric',
            'sr_6m_w_m2' => 'nullable|numeric',
        ];
    }
}
