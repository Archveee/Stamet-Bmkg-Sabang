<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SinoptikDataWrittenToExcel
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dataTimestamp;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct($dataTimestamp, $userId)
    {
        $this->dataTimestamp = $dataTimestamp;
        $this->userId = $userId;
    }
}

