<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataSinoptik;
use App\Jobs\SendSinoptikDataJob;

class SendMeteorologiSinoptik extends Command
{
    protected $signature = 'sinoptik:send-meteorologi-sinoptik';

    protected $description = 'Send meteorologi sinoptik form data to bmkgsatu';

    public function handle()
    {
        // 1. Ambil data sinoptik paling terbaru tanpa filter setting/waktu
        $sinoptikData = DataSinoptik::latest('data_timestamp')->first();

        // 2. Jika database benar-benar kosong, hentikan
        if (!$sinoptikData) {
            $this->warn('Tidak ada data meteorologi sinoptik di database.');
            return Command::SUCCESS;
        }

        // 3. Langsung eksekusi pengiriman
        SendSinoptikDataJob::dispatch($sinoptikData->id);

        $this->info("BERHASIL: Job pengiriman Sinoptik ID {$sinoptikData->id} ({$sinoptikData->data_timestamp}) telah dijalankan.");

        return Command::SUCCESS;
    }
}
