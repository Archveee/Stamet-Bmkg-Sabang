<?php

namespace App\Console\Commands;

use App\Jobs\FetchGtsMessagesJob;
use Illuminate\Console\Command;

class FetchGtsMessages extends Command
{
    protected $signature = 'gts:fetch-gts-messages';

    protected $description = 'Fetch GTS Data From BMKGSATU';

    public function handle()
    {
        # Sinoptik
        FetchGtsMessagesJob::dispatch(1);
        # METAR
        FetchGtsMessagesJob::dispatch(4);
        // TAF
        if (in_array(now()->hour, [5, 11, 17, 23])) {
            FetchGtsMessagesJob::dispatch(6);
        }

        // In the morning
        if (now()->hour == 0) {
            // WXREV
            FetchGtsMessagesJob::dispatch(8);
            // DCH
            FetchGtsMessagesJob::dispatch(53);
        }

        $this->info("Data GTS Messages berhasil diambil dari BMKGSATU");
    }
}
