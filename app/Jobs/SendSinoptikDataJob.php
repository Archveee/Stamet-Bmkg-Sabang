<?php

namespace App\Jobs;

use App\Helpers\SinoptikDataHelper;
use App\Models\DataSinoptik;
use App\Models\User;
use App\Services\BmkgsatuApiService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSinoptikDataJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sinoptikDataId;
    protected $userId;

    public $tries = 5;
    public $uniqueFor = 3600;

    public function backoff(): int
    {
        return 5;
    }

    public function __construct($sinoptikDataId, $userId = null)
    {
        $this->sinoptikDataId = $sinoptikDataId;
        $this->userId = $userId;
    }

    public function uniqueId(): string
    {
        return (string) $this->sinoptikDataId;
    }

    public function handle(BmkgsatuApiService $apiService): void
    {
        // Ambil data sinoptik
        $sinoptikData = DataSinoptik::find($this->sinoptikDataId);
        if (!$sinoptikData) {
            return;
        }

        // Mutasi data sebelum dikirim
        $sinoptikDataArray = SinoptikDataHelper::mutateSinoptikDataBeforeSent($sinoptikData->toArray());

        try {
            logger("Mengirimkan meteorologi sinoptik {$sinoptikDataArray['data_timestamp']} ke BMKG Satu v21");

            // Kirim data ke API BMKG Satu v21 (Otomatis POST / PUT jika conflict)
            $apiService->sendSinoptikData($sinoptikDataArray);

            // Kirim notifikasi
            $this->sendNotification($this->userId ?? $sinoptikData->created_by, $sinoptikData, true);
            logger("Berhasil mengirimkan meteorologi sinoptik {$sinoptikDataArray['data_timestamp']}");
        } catch (\Throwable $e) {
            logger("Gagal mengirim meteorologi sinoptik: " . $e->getMessage());
            $this->sendNotification($this->userId ?? $sinoptikData->created_by, $sinoptikData, false);
        }
    }

    protected function sendNotification($userId, $sinoptikData, $success = true): void
    {
        $user = User::find($userId);

        if (!$user) {
            return;
        }

        $timestamp = Carbon::parse($sinoptikData['data_timestamp'])->format('dHi');

        if ($success) {
            Notification::make()
                ->title("Data Sinoptik {$timestamp} berhasil terkirim")
                ->success()
                ->duration(30000)
                ->broadcast($user);
        } else {
            Notification::make()
                ->title("Data Sinoptik {$timestamp} gagal terkirim")
                ->danger()
                ->duration(30000)
                ->broadcast($user);
        }
    }
}
