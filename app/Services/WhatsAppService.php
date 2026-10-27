<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $endpoint;
    protected string $token;
    protected string $certPath;

    public function __construct()
    {
        $this->endpoint = config('services.whatsapp.endpoint');
        $this->token = config('services.whatsapp.token');
        $this->certPath = storage_path('certs/cacert.pem');
    }

    /**
     * Kirim pesan WhatsApp
     *
     * @param string|array $target Nomor WhatsApp tujuan (bisa array atau string)
     * @param string $message Pesan yang akan dikirim
     * @return bool True jika berhasil, False jika gagal
     */
    public function sendMessage(string|array $target, string $message): bool
    {
        try {
            // Jika target adalah array, ubah menjadi string dengan pemisah koma
            if (is_array($target)) {
                $target = implode(',', $target);
            }

            $waData = [
                'target' => $target,
                'message' => $message,
            ];

            $response = Http::withOptions([
                'verify' => $this->certPath,
            ])->withHeaders([
                'Authorization' => $this->token,
            ])->post($this->endpoint, $waData);

            if ($response->successful()) {
                return true;
            }

            logger('WhatsApp API Error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            logger('WhatsApp Service Exception: ' . $e->getMessage());
            return false;
        }
    }
}
