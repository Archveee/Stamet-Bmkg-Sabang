<?php

namespace App\Jobs;

use App\Filament\Resources\DataSinoptikResource;
use App\Helpers\SinoptikDataHelper;
use App\Models\User;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Actions\Action as NotificationAction;
use Illuminate\Support\Carbon;

class FetchSinoptikDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $timestamp;

    protected $userId;

    public $tries = 3;

    public function backoff(): int
    {
        return 5;
    }

    public function __construct($timestamp, $userId = null)
    {
        $this->timestamp = $timestamp;
        $this->userId = $userId;
    }


    public function handle(): void
    {
        try {
            logger("Menarik data sinoptik {$this->timestamp} dari server sinoptik");
            $response = SinoptikDataHelper::fetchDataSinoptik($this->timestamp, $this->userId);
            
            // TAMBAHKAN BARIS INI UNTUK MENGINTIP DATA DARI EXCEL:
            logger('DATA MENTAH EXCEL: ' . json_encode($response)); 

            if ($response['status'] == 'success') {
                logger("Berhasil menarik data sinoptik {$this->timestamp}");
                $this->sendNotification($this->userId, true);
            }
        } catch (\Exception $e) {

            if (isset($response)) {
                logger($response['detail']);
                $this->sendNotification($this->userId, false, $response['detail']);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $hour = Carbon::createFromFormat('YmdH', $this->timestamp)->format('H');
        // Kirim notifikasi error ke user jika ada userId
        if ($this->userId) {
            $this->sendNotification($this->userId, false, "Gagal menarik data jam {$hour} dari server sinoptik. Pastikan program sinoptik berjalan...");
        } else {
            // Send Whatsapp on background, if no user and not recheck if data exists (> minute 50)
            if (setting('bmkgsabang_whatsapp_notification_fetch_sinoptik_error') && now()->minute > 50) {
                $wa = new WhatsAppService();
                $wa->sendMessage(config('services.whatsapp.monitoring_group_id'), "Data synop jam {$hour} UTC tidak berhasil ditarik dari excel. Cek kesalahan data di PC Synop.");
            }
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
                ->title("Data Sinoptik {$this->timestamp} berhasil ditarik.")
                ->body('Klik refresh bila data belum muncul di tabel')
                ->success()
                ->duration(15000)
                ->actions([
                    NotificationAction::make('refresh')
                        ->url(DataSinoptikResource::getUrl('index'))
                ])
                ->broadcast($user);
        } else {
            Notification::make()
                ->title("Terjadi kesalahan saat mengambil data sinoptik {$this->timestamp}")
                ->body($message)
                ->danger()
                ->duration(30000)
                ->broadcast($user);
        }
    }
}