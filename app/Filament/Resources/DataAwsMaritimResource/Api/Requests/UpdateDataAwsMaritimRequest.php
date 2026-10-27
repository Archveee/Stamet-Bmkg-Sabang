<?php

namespace App\Filament\Resources\DataAwsMaritimResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDataAwsMaritimRequest extends FormRequest
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
			'idaws' => 'required|string',
			'waktu' => 'required',
			'windspeed' => 'required|numeric',
			'winddir' => 'required|numeric',
			'temp' => 'required|numeric',
			'rh' => 'required|numeric',
			'pressure' => 'required|numeric',
			'rain' => 'required|numeric',
			'solrad' => 'required|numeric',
			'netrad' => 'required|numeric',
			'watertemp' => 'required|numeric',
			'waterlevel' => 'required|numeric',
			'ta_min' => 'required|numeric',
			'ta_max' => 'required|numeric',
			'pancilevel' => 'required|numeric',
			'pancitemp' => 'required|numeric'
		];
    }
}
