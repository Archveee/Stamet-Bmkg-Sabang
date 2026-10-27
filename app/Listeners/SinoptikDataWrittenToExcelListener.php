<?php

namespace App\Listeners;

use App\Events\SinoptikDataWrittenToExcel;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class SinoptikDataWrittenToExcelListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SinoptikDataWrittenToExcel $event): void
    {
        $dataTimestamp = $event->dataTimestamp;
        $userId = $event->userId;

        $dataTimestamp = Carbon::parse($dataTimestamp)->format('YmdH');
        $user = User::find($userId);
        
        if (!$user) {
            return;
        }

        Notification::make()
            ->title("Data sinoptik {$dataTimestamp} berhasil ditulis ke excel")
            ->success()
            ->duration(30000)
            ->broadcast($user);
    }
}
