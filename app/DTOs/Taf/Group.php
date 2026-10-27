<?php

namespace App\DTOs\Taf;

use DateTimeInterface;

class Group
{
    public function __construct(
        public string $kind,
        public DateTimeInterface $start,
        public DateTimeInterface $end,
        public Condition $condition,
        public ?Condition $previous = null
    ) {}
}