<?php

namespace App\Console\Commands;

use App\Jobs\FetchAwsMaritimDataJob;
use Illuminate\Console\Command;

class FetchDataAwsMaritim extends Command
{
    protected $signature = 'awsmaritim:fetch-data';

    protected $description = 'Fetch Data From AWS Maritim Server';

    public function handle()
    {
        FetchAwsMaritimDataJob::dispatch();

        $this->info("Data AWS Maritim Berhasil Disimpan!");
    }
}
