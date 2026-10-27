<?php

namespace App\Jobs;

use App\Helpers\SinoptikDataHelper;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SendDataAwsToServerSinoptikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $timestamp;

    protected int $minute;

    protected $userId;

    public $tries = 3;

    public function backoff(): int
    {
        return 5;
    }

    public function __construct($timestamp, $minute = 50, $userId = null)
    {
        $this->timestamp = $timestamp;
        $this->minute = $minute;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        try {
            logger("Mengirim data AWS {$this->timestamp} ke server sinoptik");
            $response = SinoptikDataHelper::sendDataAwsToServerSinoptik($this->timestamp, $this->minute, $this->userId);

            if (isset($response['status']) && $response['status'] == 'success') {
                logger("Berhasil mengirim data AWS {$this->timestamp} ke excel");
                $this->sendNotification($this->userId, true);
            } else {
                $errorMessage = $response['detail'] ?? "Gagal mengirim data AWS {$this->timestamp} ke excel.";
                logger("Gagal mengirim data AWS ke server sinoptik {$this->timestamp}");
                $this->sendNotification($this->userId, false, $errorMessage);
            }
        } catch (\Throwable $e) {
            logger("Gagal mengirim data AWS ke server sinoptik {$this->timestamp}: " . $e->getMessage());
            $this->sendNotification($this->userId, false, $e->getMessage());
            throw $e;
        }
    }


    protected function sendNotification($userId, $success = true, $message = ''): void
    {
        $user = User::find($userId);

        // Sementara broadcast ke semua user
        if (!$user) {
            return;
        }

        if ($success) {
            // Notification sukses
            Notification::make()
                ->title("Data AWS {$this->timestamp} berhasil diisikan ke excel server sinoptik.")
                ->success()
                ->broadcast($user);
        } else {
            Notification::make()
                ->title("Terjadi kesalahan saat mengirim data AWS {$this->timestamp} ke server sinoptik")
                ->danger()
                ->duration(10000)
                ->broadcast($user);
        }
    }
}
