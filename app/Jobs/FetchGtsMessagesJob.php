<?php

namespace App\Jobs;

use App\Models\GtsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\BmkgsatuApiService;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class FetchGtsMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $typeMessage;
    protected $startTime;
    protected $endTime;

    public function backoff(): int
    {
        return 5;
    }

    public function __construct(
        int $typeMessage = 1,
        ?string $startTime = null,
        ?string $endTime = null
    ) {
        $this->typeMessage = $typeMessage;

        $this->startTime = $startTime
            ? Carbon::parse($startTime)->format('Y-m-d\TH:i:s')
            : now()->startOfDay()->format('Y-m-d\TH:i:s');

        $this->endTime = $endTime
            ? Carbon::parse($endTime)->format('Y-m-d\TH:i:s')
            : now()->endOfDay()->format('Y-m-d\TH:i:s');
    }

    public function handle(BmkgsatuApiService $apiService): void
    {
        try {
            $typeMessageStr = match ($this->typeMessage) {
                1 => 'SINOPTIK',
                4 => 'METAR',
                6 => 'TAF',
                8 => 'WXREV',
                53 => 'DCH',
                default => null,
            };

            if (!$typeMessageStr) {
                logger("Tipe pesan GTS tidak dikenal: {$this->typeMessage}, job tidak diproses.");
                return;
            }

            logger("Menyinkronkan monitoring GTS {$typeMessageStr} dengan bmkgsatu");

            // Need to add 30 minutes to $endTime of TAF
            if ($this->typeMessage == 6) {
                $tafEndTime = Carbon::parse($this->endTime)->addMinutes(30)->format('Y-m-d\TH:i:s');
                $response = $apiService->getGtsMessages($this->typeMessage, $this->startTime, $tafEndTime);
            } else {
                $response = $apiService->getGtsMessages($this->typeMessage, $this->startTime, $this->endTime);
            }

            if (!empty($response['items'])) {
                $createdCount = 0;
                $updatedCount = 0;

                foreach ($response['items'] as $item) {
                    $timestampData = isset($item['timestamp_data']) ? Carbon::parse($item['timestamp_data']) : null;
                    $timestampSentData = isset($item['timestamp_sent_data']) ? Carbon::parse($item['timestamp_sent_data'])->setMicrosecond(0) : null;

                    if (!$timestampData) {
                        continue;
                    }

                    $gtsData = [
                        'type_message' => $item['type_message'] ?? null,
                        'sandi_gts' => $item['sandi_gts'] ?? null,
                        'timestamp_sent_data' => $timestampSentData,
                        'is_sent' => 1,
                    ];

                    $existing = GtsMessage::whereDate('timestamp_data', $timestampData->toDateString())
                        ->whereTime('timestamp_data', $timestampData->format('H:i:s'))
                        ->where('timestamp_sent_data', $timestampSentData)
                        ->where('type_message', $this->typeMessage)
                        ->first();

                    if ($existing) {
                        $existing->update($gtsData);
                        $updatedCount++;
                    } else {
                        $gtsData['timestamp_data'] = $timestampData;
                        $gtsData['type_message'] = $this->typeMessage;
                        GtsMessage::create($gtsData);
                        $createdCount++;
                    }
                }

                logger("Berhasil menyinkronkan GTS {$typeMessageStr} dengan bmkgsatu: {$createdCount} dibuat, {$updatedCount} diperbarui.");
            } else {
                if ($this->startTime == $this->endTime) {
                    $hour = Carbon::parse($this->startTime)->format('H');

                    if (setting('bmkgsabang_whatsapp_notification_gts_not_found')) {
                        $gtsHourly = setting('bmkgsatu_meteorologi_sinoptik_gts_hourly');
                        $currentHour = now()->hour;

                        if (
                            ($this->typeMessage == 1 && $gtsHourly == 0 && $currentHour % 3 != 0) ||
                            ($this->typeMessage == 6 && !in_array($currentHour, [5, 11, 17, 23])) ||
                            ($this->typeMessage == 8 && $currentHour != 0) ||
                            ($this->typeMessage == 53 && $currentHour != 0)
                        ) {
                            return;
                        }

                        $wa = new WhatsAppService();
                        $wa->sendMessage(
                            config('services.whatsapp.monitoring_group_id'),
                            "Data GTS {$typeMessageStr} jam {$hour} UTC tidak ditemukan di bmkgsatu."
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            logger()->error("Gagal menyinkronkan pesan GTS {$this->typeMessage}: " . $e->getMessage());
        }
    }
}
