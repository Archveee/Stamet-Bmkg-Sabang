<?php

namespace App\Console\Commands;

use App\Helpers\DateHelper;
use App\Jobs\FetchSinoptikDataJob;
use Illuminate\Console\Command;

class FetchDataSinoptik extends Command
{
    protected $signature = 'sinoptik:fetch-data';

    protected $description = 'Fetch Data From Sinoptik Server';

    public function handle()
    {
        $timestamp = DateHelper::getNearestSentHour();
        $formattedTimestamp = DateHelper::formatDataTimestamp($timestamp);
        // Default userId masih null
        FetchSinoptikDataJob::dispatch($formattedTimestamp);

        $this->info("Data Sinoptik Berhasil Diambil!");
    }
}
