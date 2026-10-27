<?php

namespace App\Jobs;

use App\Helpers\GtsMessageHelper;
use App\Models\GtsMessage;
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
use Illuminate\Support\HtmlString;

class SendGtsMessageJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gtsMessageId;
    protected $userId;

    public $tries = 5;
    public $uniqueFor = 3600;

    public function backoff(): int
    {
        return 5;
    }

    public function __construct($gtsMessageId, $userId = null)
    {
        $this->gtsMessageId = $gtsMessageId;
        $this->userId = $userId;
    }

    public function uniqueId(): string
    {
        return (string) $this->gtsMessageId;
    }

    public function handle(BmkgsatuApiService $apiService): void
    {
        $gtsMessage = GtsMessage::find($this->gtsMessageId);
        if (!$gtsMessage) {
            return;
        }

        // Jalankan mutasi payload data GTS
        $gtsMessageData = GtsMessageHelper::mutateGTSMessageDataBeforeSent($gtsMessage->toArray());

        $typeMessageCode = (string) $gtsMessage->type_message;
        $typeMessageStr = GtsMessageHelper::getTypeMessageString($typeMessageCode);
        $gtsTimestampFormatted = Carbon::parse($gtsMessage->timestamp_data)->format('YmdH');

        try {
            logger("Mengirimkan GTS {$typeMessageStr} {$gtsTimestampFormatted} ke bmkgsatu v21");

            $response = $apiService->sendGtsMessage($gtsMessageData);

            if (!$response->successful()) {
                // Handling Khusus untuk HTTP Status 403 (No Permissions)
                if ($response->status() === 403) {
                    $err403Msg = "Akses ditolak BMKG Satu: Akun Anda tidak memiliki permission untuk endpoint GTS {$typeMessageStr}.";
                    logger()->error("Gagal mengirim GTS {$typeMessageStr} {$gtsTimestampFormatted}: 403 Forbidden - {$err403Msg}");

                    // Notifikasi ke User
                    $this->sendNotification($this->userId ?? $gtsMessage->created_by, $gtsMessage, false, $err403Msg);

                    // Batalkan retry karena 403 tidak akan sukses dengan dikirim ulang
                    return;
                }

                // Error HTTP lainnya (400, 500, 502, dll) akan dilempar ke catch untuk di-retry
                throw new \Exception("Gagal membuat pesan GTS {$typeMessageStr} di bmkgsatu. Status: " . $response->status() . " - Body: " . $response->body());
            }

            // Ambil nama pesan jika tersedia di respons
            $createdGtsMessageName = $response->json('@name');
            $createdGtsMessageDetail = null;

            if (!empty($createdGtsMessageName)) {
                $createdGtsMessageDetail = $apiService->getCreatedGtsMessageDetail($createdGtsMessageName);
            }

            // Update status pengiriman
          // Update status pengiriman
$gtsMessage->update([
    'is_sent' => true,
    'timestamp_sent_data' => $createdGtsMessageDetail['timestamp_sent_data'] ?? now()->toIso8601String(),
]);

            logger("Berhasil mengirimkan GTS {$typeMessageStr} {$gtsTimestampFormatted}");

            // Kirim notifikasi sukses
            $this->sendNotification($this->userId ?? $gtsMessage->created_by, $gtsMessage);

        } catch (\Throwable $e) {
            logger("Gagal mengirim GTS {$typeMessageStr} {$gtsTimestampFormatted}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $gtsMessage = GtsMessage::find($this->gtsMessageId);
        if (!$gtsMessage) {
            return;
        }

        $typeMessageStr = GtsMessageHelper::getTypeMessageString($gtsMessage->type_message);
        $timestamp = Carbon::parse($gtsMessage->timestamp_data)->format('dHi');

        $targetUserId = $this->userId ?? $gtsMessage->created_by;

        if ($targetUserId) {
            // Operkan pesan exception sebenarnya ke fungsi notifikasi
            $this->sendNotification($targetUserId, $gtsMessage, false, $exception->getMessage());
        } else {
            logger("Gagal mengirimkan GTS {$typeMessageStr} {$timestamp} ke bmkgsatu setelah beberapa kali percobaan.");
        }
    }

    protected function sendNotification($userId, GtsMessage $gtsMessage, $success = true, string $errorMessage = ''): void
    {
        $user = User::find($userId);

        if (!$user) {
            return;
        }

        $timestamp = Carbon::parse($gtsMessage->timestamp_data)->format('dHi');
        $typeMessage = GtsMessageHelper::getTypeMessageString($gtsMessage->type_message);
        $sandi = $gtsMessage->sandi_gts;

        if (!$success) {
            // Gunakan pesan error dinamis
            $bodyText = !empty($errorMessage)
                ? e($errorMessage)
                : "Gagal mengirimkan pesan {$typeMessage} ke BMKG Satu.";

            Notification::make()
                ->title("{$typeMessage} {$timestamp} gagal terkirim")
                ->body(new HtmlString('<pre style="white-space: pre-wrap; word-wrap: break-word;">' . $bodyText . '</pre>'))
                ->danger()
                ->duration(30000)
                ->broadcast($user);

            return;
        }

        Notification::make()
            ->title("{$typeMessage} {$timestamp} berhasil terkirim")
            ->body(new HtmlString('<pre style="white-space: pre-wrap; word-wrap: break-word;">' . e($sandi) . '</pre>'))
            ->success()
            ->duration(30000)
            ->broadcast($user);
    }
}

