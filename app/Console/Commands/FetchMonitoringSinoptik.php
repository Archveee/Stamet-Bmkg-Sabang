<?php

namespace App\Console\Commands;

use App\Jobs\FetchMonitoringSinoptikJob;
use Illuminate\Console\Command;

class FetchMonitoringSinoptik extends Command
{
    protected $signature = 'sinoptik:fetch-monitoring';

    protected $description = 'Fetch Monitoring Sinoptik Data From BMKGSATU';

    public function handle()
    {
        FetchMonitoringSinoptikJob::dispatch();

        $this->info("Data Monitoring Sinoptik berhasil diambil dari BMKGSATU");
    }
}
