<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class FetchWeatherImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function backoff(): int
    {
        return 10;
    }

    public function __construct(
        private string $url,
        private string $folder
    ) {}

    public function handle(): void
    {
        try {
            File::ensureDirectoryExists(public_path($this->folder));
            $response = Http::withoutRedirecting()->head($this->url);
            $lastModified = $response->header('Last-Modified');

            $timestamp = Carbon::parse($lastModified)->format('YmdHis');
            $filename = public_path("{$this->folder}/{$timestamp}.png");

            if (!file_exists($filename)) {

                $imageResponse = Http::get($this->url);

                if ($imageResponse->successful()) {
                    $newImageContent = $imageResponse->body();
                    $newHash = hash('sha256', $newImageContent);

                    // get latest image
                    $files = collect(File::glob(public_path("{$this->folder}/*.png")))
                        ->sortDesc();

                    if ($files->isNotEmpty()) {
                        $lastFile = $files->first();
                        $lastHash = hash_file('sha256', $lastFile);
                        if ($newHash === $lastHash) {
                            return;
                        }
                    }

                    File::put($filename, $newImageContent);
                }
            }
        } catch (\Throwable $e) {
            logger("Gagal mengambil data citra.");
        }
    }
}
