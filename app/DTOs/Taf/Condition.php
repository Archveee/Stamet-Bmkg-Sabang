<?php

namespace App\DTOs\Taf;

class Condition
{
    public ?int $windDir = null;
    public ?int $windSpeed = null;
    public ?bool $gust = null;
    public ?int $visibility = null;
    public ?string $precipCode = null;
    public ?string $cloud = null;
    public ?int $ceilingFt = null;

    public function merged(Condition $newer): Condition
    {
        $out = clone $this;
        foreach (get_object_vars($newer) as $prop => $val) {
            if ($val !== null && $val !== '') {
                $out->$prop = $val;
            }
        }
        return $out;
    }
}