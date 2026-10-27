<?php

namespace App\Filament\Resources\PeralatanResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePeralatanRequest extends FormRequest
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
    public function rules()
    {
        return [
            'nama_alat' => 'nullable|string|max:255',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'kondisi' => 'nullable|string|max:255',
            'tahun_pengadaan' => 'nullable|integer|digits:4',
            'monitoring_tidak' => 'nullable|boolean',
            'keterangan' => 'nullable|string|max:255',
            'last_update' => 'nullable|string|max:255',
            'sort' => 'nullable|integer',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ];
    }
}
