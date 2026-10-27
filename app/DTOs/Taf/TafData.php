<?php

namespace App\DTOs\Taf;

use DateTimeInterface;

class TafData
{
    /** @var Group[] */
    public array $groups = [];

    public function __construct(
        public string $raw,
        public DateTimeInterface $issued,
        public DateTimeInterface $validStart,
        public DateTimeInterface $validEnd,
        array $groups = [],
        public bool $isCorrection = false,
        public bool $isAmendment = false,
        public bool $isHeaderInvalid = false,
        public bool $isScheduleInvalid = false
    ) {
        $this->groups = $groups;
    }
}