<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GtsMessage;
use App\Services\DchComposer;

class GenerateGtsDch extends Command
{
    protected $signature = 'gts:generate-dch';

    protected $description = 'Generate DCH GTS Message';

    public function handle()
    {
        $date = now()->startOfDay(); # triggered at -x minute before 00 UTC

        logger("Membuat GTS DCH {$date->toDateString()}");
        $message = DchComposer::composeDchMessage($date);

        if ($message) {
            // Create GTS Message
            GtsMessage::firstOrCreate(
                ['timestamp_data' => $date, 'type_message' => '53'],
                ['sandi_gts' => $message]
            );
            logger("Berhasil membuat GTS DCH {$date->toDateString()}");
        } else {
            logger("Gagal membuat GTS DCH {$date->toDateString()}");
        }
    }
}
