<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class FetchSinoptikServerScreenshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle()
    {
        $sinoptikServerUrl = setting("excel_server_url") . "/sinoptik/screenshot";

        try {
           
            $response = Http::withToken(setting('excel_server_token'))
                ->get($sinoptikServerUrl);

            return $response->json();

        } catch (\Throwable $e) {
            logger("Terjadi kesalahan saat mengambil screenshot server sinoptik");
        }
    }
}
