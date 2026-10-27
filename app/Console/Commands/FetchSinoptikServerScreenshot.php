<?php

namespace App\Console\Commands;

use App\Jobs\FetchSinoptikServerScreenshotJob;
use Illuminate\Console\Command;

class FetchSinoptikServerScreenshot extends Command
{
    protected $signature = 'sinoptik:fetch-screenshot';

    protected $description = 'Fetch Screenshot of Sinoptik Server';

    public function handle()
    {
        FetchSinoptikServerScreenshotJob::dispatch();

        $this->info("Screenshot server sinoptik berhasil diambil!");
    }
}
