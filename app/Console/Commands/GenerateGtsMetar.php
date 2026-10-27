<?php

namespace App\Console\Commands;

use App\Helpers\DateHelper;
use Illuminate\Console\Command;
use App\Models\DataSinoptik;
use App\Models\GtsMessage;
use App\Services\MetarComposer;
use Illuminate\Support\Carbon;

class GenerateGtsMetar extends Command
{
    protected $signature = 'gts:generate-metar';

    protected $description = 'Generate METAR GTS Message';

    public function handle()
    {
        $dataTimestamp = DateHelper::getNearestSentHour();
        $dataSinoptik = DataSinoptik::where('data_timestamp', $dataTimestamp)->first();

        if (!$dataSinoptik) {
            $this->error('Data sinoptik untuk pembuatan METAR tidak tersedia.');
            return;
        }

        $dataTimestampFormatted = Carbon::parse($dataSinoptik->data_timestamp)->format('YmdH'); 

        logger("Membuat GTS METAR {$dataTimestampFormatted}");
        $message = MetarComposer::composeMetarMessage($dataSinoptik);

        // Create GTS Message
        GtsMessage::firstOrCreate(
            ['timestamp_data' => $dataSinoptik->data_timestamp, 'type_message' => '4'],
            ['sandi_gts' => $message]
        );        

        logger("Berhasil membuat GTS METAR {$dataTimestampFormatted}");
    }
}
