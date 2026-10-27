<?php

namespace App\Models;

use App\Helpers\SinoptikDataHelper;
use Illuminate\Database\Eloquent\Model;
// use Wildside\Userstamps\Userstamps;


class DataSinoptik extends Model
{
    // use Userstamps;

    protected $table = 'data_sinoptik';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'data_timestamp' => 'datetime',
    ];

    /**
     * Fungsi accessor bersama untuk akses float 1 digit dibelakang koma.
     */
    private function roundableAttribute(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->roundValue($value),
            set: fn($value) => $this->roundValue($value),
        );
    }

    protected function roundValue(?float $value, int $precision = 1): ?float
    {
        return $value !== null ? round($value, $precision) : null;
    }

    protected function pressureQffMbDerived(): Attribute
    {
        return $this->roundableAttribute();
    }

    protected function pressureQfeMbDerived(): Attribute
    {
        return $this->roundableAttribute();
    }

    protected function tempDewpointCTdtdtd(): Attribute
    {
        return $this->roundableAttribute();
    }

    protected function tempDrybulbCTttttt(): Attribute
    {
        return $this->roundableAttribute();
    }

    protected function tempWetbulbC(): Attribute
    {
        return $this->roundableAttribute();
    }


    /**
     * Fungsi accessor bersama untuk rainfall.
     */
    private function rainfallAccessor(): Attribute
    {
        return Attribute::make(
            get: fn($value) => match ($value) {
                0.0 => 0,
                8888.0 => 8888,
                9999.0 => 9999,
                default => $value,
            }
        );
    }

    protected function rainfall24hRrr(): Attribute
    {
        return $this->rainfallAccessor();
    }

    protected function rainfall6hRrr(): Attribute
    {
        return $this->rainfallAccessor();
    }

    protected function rainfallLastMm(): Attribute
    {
        return $this->rainfallAccessor();
    }
}
