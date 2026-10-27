<?php

namespace App\Console\Commands;

use App\Helpers\DateHelper;
use Illuminate\Console\Command;
use App\Models\DataSinoptik;
use App\Models\GtsMessage;
use App\Services\SinoptikComposer;
use Illuminate\Support\Carbon;

class GenerateGtsSinoptik extends Command
{
    protected $signature = 'gts:generate-sinoptik';

    protected $description = 'Generate Sinoptik GTS Message';

    public function handle()
    {
        $gtsHourly = setting('bmkgsatu_meteorologi_sinoptik_gts_hourly'); // 0 = per 3 jam, 1 = per jam
        $dataTimestamp = DateHelper::getNearestSentHour(); // Mengembalikan instance Carbon
        $dataHour = $dataTimestamp->hour; // Ambil jam langsung dari Carbon

        // Jika per 3 jam, hanya buat GTS jika jamnya kelipatan 3
        if ($gtsHourly == 0 && $dataHour % 3 !== 0) {
            return;
        }

        // Ambil data sinoptik berdasarkan timestamp
        $dataSinoptik = DataSinoptik::where('data_timestamp', $dataTimestamp)->first();

        if (!$dataSinoptik) {
            $this->error('Data sinoptik untuk pembuatan GTS tidak tersedia.');
            return;
        }

        $dataTimestampFormatted = Carbon::parse($dataSinoptik->data_timestamp)->format('YmdH'); 

        // Buat pesan GTS Sinoptik
        logger("Membuat GTS sinoptik {$dataTimestampFormatted}");
        $message = SinoptikComposer::composeSinoptikMessage($dataSinoptik);

        GtsMessage::firstOrCreate(
            ['timestamp_data' => $dataSinoptik->data_timestamp, 'type_message' => '1'],
            ['sandi_gts' => $message]
        );

        logger("Berhasil membuat GTS sinoptik {$dataTimestampFormatted}");
    }
}
