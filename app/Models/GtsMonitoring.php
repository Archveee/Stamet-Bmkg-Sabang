<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class GtsMonitoring extends Model
{
    protected $table = 'gts_monitoring';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'sinoptik_late_hours' => 'array',
        'sinoptik_missing_hours' => 'array',
        'sinoptik_invalid_hours' => 'array',
        'metar_late_hours' => 'array',
        'metar_missing_hours' => 'array',
        'metar_invalid_hours' => 'array',
        'taf_late_hours' => 'array',
        'taf_missing_hours' => 'array',
        'taf_invalid_hours' => 'array',
    ];

    protected function sinoptikSentPercent(): Attribute
    {
        return Attribute::get(
            fn() => round((8 - count($this->sinoptik_missing_hours ?? [])) / 8 * 100, 1)
        );
    }

    protected function sinoptikOntimePercent(): Attribute
    {
        return Attribute::get(
            fn() => round((8 - count($this->sinoptik_late_hours ?? [])) / 8 * 100, 1)
        );
    }

    protected function metarSentPercent(): Attribute
    {
        return Attribute::get(
            fn() => round((24 - count($this->metar_missing_hours ?? [])) / 24 * 100, 1)
        );
    }

    protected function metarOntimePercent(): Attribute
    {
        return Attribute::get(
            fn() => round((24 - count($this->metar_late_hours ?? [])) / 24 * 100, 1)
        );
    }



    protected function tafSentPercent(): Attribute
    {
        return Attribute::get(
            fn() => round((4 - count($this->taf_missing_hours ?? [])) / 4 * 100, 1)
        );
    }

    protected function tafOntimePercent(): Attribute
    {
        return Attribute::get(
            fn() => round((4 - count($this->taf_late_hours ?? [])) / 4 * 100, 1)
        );
    }

    // Validitas sandi

    protected function sinoptikValidPercent(): Attribute
    {
        return Attribute::get(
            fn() => is_array($this->sinoptik_invalid_hours)
                ? round((8 - count($this->sinoptik_invalid_hours)) / 8 * 100, 1)
                : null
        );
    }

    protected function metarValidPercent(): Attribute
    {
        return Attribute::get(
            fn() => is_array($this->metar_invalid_hours)
                ? round((24 - count($this->metar_invalid_hours)) / 24 * 100, 1)
                : null
        );
    }

    protected function tafValidPercent(): Attribute
    {
        return Attribute::get(
            fn() => is_array($this->taf_invalid_hours)
                ? round((4 - count($this->taf_invalid_hours)) / 4 * 100, 1)
                : null
        );
    }
}
