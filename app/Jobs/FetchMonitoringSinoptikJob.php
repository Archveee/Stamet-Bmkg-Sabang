<?php

namespace App\Jobs;

use App\Models\MonitoringSinoptik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\BmkgsatuApiService;
use Carbon\Carbon;

class FetchMonitoringSinoptikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $periode;

    public function backoff(): int
    {
        return 5;
    }

    public function __construct(?string $periode = null)
    {
        // Jika periode tidak diberikan, gunakan tanggal hari ini
        $this->periode = $periode ?? Carbon::now()->format('Y-m-d');
    }

    public function handle(BmkgsatuApiService $apiService): void
    {
        try {
            logger("Menyinkronkan monitoring sinoptik {$this->periode} dengan bmkgsatu");

            $response = $apiService->getMonitoringSinoptikData($this->periode);

            if (!empty($response) && is_array($response) && count($response) > 0) {
                $stationData = $response[0];

                $hourlyData = [];

                foreach ($stationData as $data) {
                    if (
                        isset($data['key'], $data['value']) &&
                        preg_match('/^(0[0-9]|1[0-9]|2[0-3]):00$/', $data['key'])
                    ) {
                        $hourlyData[$data['key']] = $data['value'];
                    }
                }

                MonitoringSinoptik::updateOrCreate(
                    [
                        'periode' => $this->periode,
                    ],
                    [
                        'hourly_data' => $hourlyData
                    ]
                );

                logger("Berhasil menyinkronkan monitoring sinoptik {$this->periode}");

                // Cek jika ada jam sebelum waktu saat ini UTC yang value-nya 0 (belum tersedia)
                $nowUtcHour = now()->format('H:00');
                $missingHours = [];

                foreach ($hourlyData as $hour => $value) {
                    if ($value == 0 && $hour <= $nowUtcHour) {
                        $missingHours[] = $hour;
                    }
                }

                // Kirim WhatsApp kalau ada data yang bolong untuk pengecekan di menit ke 10 (otomatis dari console)
                if (!empty($missingHours) && setting('bmkgsabang_whatsapp_notification_monitoring_sinoptik_not_found') && now()->minute == 10) {
                    $hourList = implode(', ', $missingHours);
                    $wa = new \App\Services\WhatsAppService();
                    $wa->sendMessage(
                        config('services.whatsapp.monitoring_group_id'),
                        "Data meteorologi sinoptik jam {$hourList} UTC tidak ditemukan di bmkgsatu."
                    );
                }
            } else {
                logger("Data monitoring sinoptik {$this->periode} tidak ditemukan di bmkgsatu.");
            }
        } catch (\Exception $e) {
            logger("Gagal menyinkronkan monitoring sinoptik {$this->periode}: " . $e->getMessage());
            throw $e;
        }
    }
}
