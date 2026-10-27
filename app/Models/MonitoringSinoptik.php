<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class MonitoringSinoptik extends Model
{
    protected $table = 'monitoring_sinoptik';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'periode' => 'date',
        'hourly_data' => 'array',
    ];

    protected function progress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $hourlyData = $this->hourly_data ?? [];

                // Hitung jumlah data yang bernilai 1
                $received = collect($hourlyData)->filter(fn($value) => $value == 1)->count();
                $total = 24; // Total jam dalam sehari
                
                // Hitung persentase
                $percentage = ($total > 0) ? ($received / $total) * 100 : 0;

                return number_format($percentage, 1);
            }
        );
    }
}
