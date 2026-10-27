<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\DataAwsMaritim;


class FetchAwsMaritimDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function backoff(): int
    {
        return 5;
    }

    public function __construct() {}

    public function handle(): void
    {
        $urlAwsMaritim = 'http://202.90.199.132/aws-new/data/station/latest/3000000038';

        try {
            $response = Http::get($urlAwsMaritim);

            if ($response->successful()) {
                $data = $response->json();

                DataAwsMaritim::updateOrCreate(
                    ['waktu' => $data['waktu']],
                    $data
                );
            }
        } catch (\Throwable $e) {
            logger("Gagal mengambil data aws maritim.");
        }
    }
}
