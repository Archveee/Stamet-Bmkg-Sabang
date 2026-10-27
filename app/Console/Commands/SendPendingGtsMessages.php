<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GtsMessage;
use App\Jobs\SendGtsMessageJob;

class SendPendingGtsMessages extends Command
{
    protected $signature = 'gts:send-pending-messages';

    protected $description = 'Send pending GTS messages that are due for sending';

    public function handle()
    {
        // Ambil semua pesan GTS yang belum terkirim
        $pendingMessages = GtsMessage::where('is_sent', 0)
            ->where('timestamp_data', '<=', now())
            ->where('timestamp_data', '>=', now()->subDay())
            ->get();

        foreach ($pendingMessages as $message) {
            try {
                // Eksekusi pengiriman secara LANGSUNG (Sync)
                SendGtsMessageJob::dispatchSync($message->id);
                logger("GTS ID {$message->id} (Tipe {$message->type_message}) berhasil dikirim secara langsung.");
            } catch (\Throwable $e) {
                // TANGKAP ERROR: Agar jika 1 pesan error (misal DCH invalid), pesan lainnya TETAP TERKIRIM
                logger()->error("Gagal mengirim GTS ID {$message->id} (Tipe {$message->type_message}): " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
