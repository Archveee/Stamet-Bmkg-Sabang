<?php

namespace App\Jobs;

use App\Models\GtsMonitoring;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\BmkgsatuApiService;
use Carbon\Carbon;

class FetchGtsMonitoringDataJob implements ShouldQueue
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
        int $typeMessage,
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
                default => null,
            };

            if (!$typeMessageStr) {
                return;
            }

            logger("Menarik data monitoring GTS {$typeMessageStr} dari bmkgsatu");

            $response = $apiService->getGtsMessages($this->typeMessage, $this->startTime, $this->endTime);

            if (empty($response['items'])) {
                return;
            }

            // Struktur penyimpanan sementara per tanggal
            $perDayData = [];

            foreach ($response['items'] as $item) {
                $timestampRaw = $item['timestamp_data'] ?? null;
                if (!$timestampRaw) continue;

                $timestampData = Carbon::parse($timestampRaw);
                $timestampSentData = isset($item['timestamp_sent_data']) ? Carbon::parse($item['timestamp_sent_data']) : null;

                $date = $timestampData->format('Y-m-d');
                $hour = $timestampData->format('H');

                // Inisialisasi jika tanggal belum disiapkan
                if (!isset($perDayData[$date])) {
                    $expectedHours = match ($typeMessageStr) {
                        'SINOPTIK' => ['00', '03', '06', '09', '12', '15', '18', '21'],
                        'METAR' => collect(range(0, 23))->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT))->toArray(),
                        'TAF' => ['05', '11', '17', '23'],
                        default => [],
                    };

                    $perDayData[$date] = [
                        'missing' => $expectedHours,
                        'late' => [],
                        'invalid' => [],
                    ];
                }

                // Hapus dari missing jika ditemukan
                if (($key = array_search($hour, $perDayData[$date]['missing'])) !== false) {
                    unset($perDayData[$date]['missing'][$key]);
                }

                // Tandai keterlambatan
                if ($timestampSentData) {
                    $expectedSentTime = (clone $timestampData)->addMinutes(10);
                    if ($timestampSentData->gt($expectedSentTime)) {
                        if (!in_array($hour, $perDayData[$date]['late'])) {
                            $perDayData[$date]['late'][] = $hour;
                        }
                    }
                }

                // Cek validitas belum dibuat

            }

            // Simpan ke database
            foreach ($perDayData as $date => $hourData) {
                $gtsMonitoring = GtsMonitoring::firstOrCreate(['date' => $date]);

                $missingField = strtolower($typeMessageStr) . '_missing_hours';
                $lateField = strtolower($typeMessageStr) . '_late_hours';
                $invalidField = strtolower($typeMessageStr) . '_invalid_hours';

                $gtsMonitoring->$missingField = array_values($hourData['missing']);
                $gtsMonitoring->$lateField = array_values($hourData['late']);
                // $gtsMonitoring->$invalidField = array_values($hourData['invalid']);
                $gtsMonitoring->$invalidField = null;
                $gtsMonitoring->save();
            }

            logger("Berhasil menyimpan data monitoring GTS {$typeMessageStr} dari bmkgsatu");
        } catch (\Exception $e) {
            logger("Gagal menarik data monitoring GTS dari bmkgsatu: " . $e->getMessage());
        }
    }
}
