<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GtsMessage;
use App\Services\WxrevComposer;

class GenerateGtsWxrev extends Command
{
    protected $signature = 'gts:generate-wxrev';

    protected $description = 'Generate WXREV GTS Message';

    public function handle()
    {
        $date = now()->startOfDay(); # triggered at -x minute before 00 UTC

        logger("Membuat GTS WXREV {$date->toDateString()}");
        $message = WxrevComposer::composeWxrevMessage($date);

        if ($message) {
            // Create GTS Message
            GtsMessage::firstOrCreate(
                ['timestamp_data' => $date, 'type_message' => '8'],
                ['sandi_gts' => $message]
            );
            logger("Berhasil membuat GTS WXREV {$date->toDateString()}");
        } else {
            logger("Gagal membuat GTS WXREV {$date->toDateString()}");
        }
    }
}
