<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class DeleteOldWeatherImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    /**
     * @param string|array $folders Folder relatif terhadap public_path()
     */
    public function __construct(
        private string|array $folders
    ) {}

    public function handle(): void
    {
        $folders = is_array($this->folders) ? $this->folders : [$this->folders];
        $now = Carbon::now();

        logger("Menghapus file citra yang lebih dari sebulan.");

        foreach ($folders as $relativeFolder) {
            $path = public_path($relativeFolder);

            if (!File::isDirectory($path)) {
                continue;
            }

            $files = File::files($path);

            foreach ($files as $file) {
                $filename = $file->getFilename();

                if (!preg_match('/^\d{14}\.png$/', $filename)) continue;

                $timestampStr = substr($filename, 0, 14);

                try {
                    $fileTime = Carbon::createFromFormat('YmdHis', $timestampStr);
                    if ($fileTime->diffInDays($now) > 30) {
                        File::delete($file->getRealPath());
                    }
                } catch (\Exception $e) {
                    // nothing
                }
            }
        }
    }
}
