<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataAwsRequest extends FormRequest
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
            'wind_dir_deg_dd' => 'nullable|integer',
            'wind_speed_ff' => 'nullable|numeric',
            'wind_speed_ff_maks' => 'nullable|numeric',
            'pressure_qfe_mb_derived' => 'nullable|numeric',
            'pressure_qff_mb_derived' => 'nullable|numeric',
            'temp_drybulb_c_tttttt' => 'nullable|numeric',
            'temp_dewpoint_c_tdtdtd' => 'nullable|numeric',
            'relative_humidity_pc' => 'nullable|integer',
            'rainfall_last_mm' => 'nullable|numeric',
            'temp_max_c_txtxtx' => 'nullable|numeric',
            'temp_min_c_tntntn' => 'nullable|numeric',
        ];
    }
}
