<?php

namespace App\DTOs\Taf;

use DateTimeInterface;

class ObservationData
{
    public function __construct(
        public string $raw,
        public DateTimeInterface $at,
        public string $kind,
        public Condition $condition,
        public bool $isCorrection = false
    ) {}
}