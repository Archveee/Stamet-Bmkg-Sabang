<?php

namespace App\Filament\Pages;

use App\Services\BmkgAviationApiService;
use App\Services\TafReportExcelService;
use App\Services\TafVerifierService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class VerifyTafPage extends Page implements HasActions
{
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static string $view = 'filament.pages.verify-taf-page';
    protected static ?string $title = 'Verifikasi TAF Realtime';
    protected static ?string $slug = 'verify-taf-page';

    public string $selectedDate;
    public string $icao = 'WITN';

    public string $rawTaf = '';
    public array $qcResult = [];

    public function checkQualityControl(): void
    {
        $rawTaf = trim(preg_replace('/\s+/', ' ', str_replace('=', ' = ', strtoupper($this->rawTaf))));
        $clean = trim(str_replace(' =', '=', $rawTaf));
        
        if (empty($clean)) {
            $this->qcResult = [];
            return;
        }

        $results = [];
        $errors = 0;

        $addResult = function($element, $rule, $remark, $isValid) use (&$results, &$errors) {
            $results[] = [
                'element' => $element,
                'rule' => $rule,
                'remark' => $remark,
                'is_valid' => $isValid
            ];
            if (!$isValid) {
                $errors++;
            }
        };

        $getContinuousHour = function($day, $hour, $baseDay) {
            $diff = $day - $baseDay;
            if ($diff < -15) $diff += 30; 
            if ($diff > 15) $diff -= 30;  
            return $diff * 24 + $hour;
        };

        // 1. Header & ICAO
        $requiredHeaderOk = (bool) preg_match('/^FTID40\s+WITN\b/', $clean);
        $addResult(
            "FTID40 WITN", 
            "QC Header TAF", 
            $requiredHeaderOk ? "Header awal sesuai ketentuan: FTID40 WITN." : "TAF TIDAK VALID: header awal bukan FTID40 WITN.", 
            $requiredHeaderOk
        );

        preg_match('/\b(TAF(?:\s+(?:AMD|COR))?)\s+(WITN)\s+(\d{2})(\d{2})(\d{2})Z\s+(\d{2})(\d{2})\/(\d{2})(\d{2})\b/', $clean, $headerMatch);
        
        $issueDay = 0;
        
        if ($headerMatch) {
            $tafType = $headerMatch[1];
            $icao = $headerMatch[2];
            
            $issueDay = (int)$headerMatch[3];
            $issueHour = (int)$headerMatch[4];
            
            $startDay = (int)$headerMatch[6];
            $startHour = (int)$headerMatch[7];
            
            $endDay = (int)$headerMatch[8];
            $endHour = (int)$headerMatch[9];

            $addResult("$tafType WITN", "Identifikasi TAF", "Header TAF dan jenis sandi dikenali.", true);
            
            // ATURAN CCA/AAA (SESUAI EVALUASI SENIOR)
            if (str_contains($tafType, 'COR') && !preg_match('/^FTID40\s+WITN\s+\d{6}\s+CC[A-Z]/', $clean)) {
                $addResult("Header TAF COR", "Kelompok BBB (Koreksi)", "Sandi TAF COR harus mencantumkan indikator CCA (atau CCB, CCC) pada baris heading FTID40 WITN.", false);
            } elseif (str_contains($tafType, 'AMD') && !preg_match('/^FTID40\s+WITN\s+\d{6}\s+AA[A-Z]/', $clean)) {
                $addResult("Header TAF AMD", "Kelompok BBB (Amandemen)", "Sandi TAF AMD harus mencantumkan indikator AAA (atau AAB, AAC) pada baris heading FTID40 WITN.", false);
            } else {
                $addResult($tafType, "Jenis prakiraan", "Jenis prakiraan (beserta kelompok BBB jika ada) valid.", true);
            }
            
            $addResult($icao, "Indikator lokasi", "Indikator lokasi (WITN) valid.", true);
            $addResult($headerMatch[3] . $headerMatch[4] . $headerMatch[5] . 'Z', "Waktu Penerbitan", "Format Waktu Penerbitan Valid.", true);

            $issueTotal = $getContinuousHour($issueDay, $issueHour, $issueDay);
            $startTotal = $getContinuousHour($startDay, $startHour, $issueDay);
            $endTotal = $getContinuousHour($endDay, $endHour, $issueDay);
            
            if ($startDay === $endDay && $startHour === $endHour) {
                $endTotal += 24;
            }
            
            $durationHours = $endTotal - $startTotal;
            $diffIssueToStart = $startTotal - $issueTotal;

            $periodString = "{$headerMatch[6]}{$headerMatch[7]}/{$headerMatch[8]}{$headerMatch[9]}";

            if (!str_contains($tafType, 'AMD') && !str_contains($tafType, 'COR')) {
                if ($diffIssueToStart < 0) {
                    $addResult(
                        $periodString, 
                        "Periode Validitas TAF Rutin", 
                        "Waktu mulai validitas ($startDay $startHour:00) terdeteksi berada di masa lalu dibandingkan waktu terbit (Tanggal $issueDay Jam $issueHour:00). Pastikan penulisan tanggal periode (DD) sudah benar.", 
                        false
                    );
                } elseif ($diffIssueToStart !== 1 || $durationHours !== 24) {
                    $addResult(
                        $periodString, 
                        "Periode Validitas TAF Rutin", 
                        "TAF Rutin harus mulai 1 jam setelah issue dan berdurasi tepat 24 jam. Durasi terdeteksi: $durationHours jam, Jeda issue ke start: $diffIssueToStart jam.", 
                        false
                    );
                } else {
                    $addResult($periodString, "Periode Validitas TAF Rutin", "Periode TAF valid (berdurasi tepat 24 jam).", true);
                }
            } else {
                if ($durationHours > 0 && $durationHours <= 24) {
                    $addResult($periodString, "Periode Validitas TAF AMD/COR", "TAF AMD/COR valid (durasi $durationHours jam).", true);
                } else {
                    $addResult($periodString, "Periode Validitas TAF AMD/COR", "Durasi TAF AMD/COR tidak logis ($durationHours jam).", false);
                }
            }
        } else {
            $addResult("Header", "Identifikasi TAF", "Header tidak dikenali (Cek penulisan TAF WITN DDHHMMZ DDHH/DDHH).", false);
            preg_match('/\b(\d{2})(\d{2})(\d{2})Z\b/', $clean, $fallbackTime);
            $issueDay = $fallbackTime ? (int)$fallbackTime[1] : 1;
        }

        // 2. Cek Token/Angka Berlebih (Visibility, Wind, Cloud)
        $cleanForTokens = preg_replace('/^FTID40\s+WITN\s+\d{6}\b/', '', $clean);
        $cleanWithoutTimes = preg_replace('/\b\d{2}\d{2}\/\d{2}\d{2}\b/', '', $cleanForTokens);
        $cleanWithoutTimes = preg_replace('/\b\d{6}Z\b/', '', $cleanWithoutTimes);

        $tokens = explode(' ', str_replace(['=', 'FTID40', 'WITN', 'TAF', 'AMD', 'COR', 'BECMG', 'TEMPO', 'INTER', 'PROB30', 'PROB40'], '', $cleanWithoutTimes));
        foreach ($tokens as $token) {
            $token = trim($token);
            if (empty($token)) continue;

            if (preg_match('/^\d+$/', $token)) {
                if (strlen($token) !== 4) {
                    $addResult($token, "Validasi Jumlah Digit Visibility", "Format visibility harus tepat 4 digit (contoh: 8000). Ditemukan: $token (" . strlen($token) . " digit).", false);
                }
            }

            if (str_contains($token, 'KT')) {
                if (!preg_match('/^\b(?:VRB|\d{3})\d{2,3}(?:G\d{2,3})?KT\b$/', $token)) {
                    $addResult($token, "Validasi Digit Angin", "Format angin tidak valid/kelebihan digit. Ditemukan: $token.", false);
                }
            }

            if (preg_match('/^(FEW|SCT|BKN|OVC)(\d+)(CB|TCU)?$/', $token, $cloudDigitMatch)) {
                if (strlen($cloudDigitMatch[2]) !== 3) {
                    $addResult($token, "Validasi Tinggi Awan", "Tinggi dasar awan harus tepat 3 digit (contoh: FEW010). Ditemukan: $token.", false);
                }
            }
        }

        // Pembagian Grup 
        $bodyForOrder = $headerMatch ? trim(substr($clean, strlen($headerMatch[0]) + strpos($clean, $headerMatch[0]))) : $clean;
        preg_match_all('/\b(BECMG|TEMPO|INTER|PROB30|PROB40|FM\d{6})\b/', $bodyForOrder, $markers, PREG_OFFSET_CAPTURE);
        
        $orderChunks = [];
        $firstEnd = $markers[0] ? $markers[0][0][1] : strlen($bodyForOrder);
        $orderChunks[] = ["GENERAL", substr($bodyForOrder, 0, $firstEnd)];
        
        $oi = 0;
        $markerCount = count($markers[0] ?? []);
        
        $parsedGroupsForOverlap = []; 
        $currentBaseVis = null; 

        $genTextNoTime = preg_replace('/\b\d{4}\/\d{4}\b/', '', preg_replace('/\b\d{6}Z\b/', '', $orderChunks[0][1]));
        if (preg_match('/\b(\d{4}|CAVOK)\b/', $genTextNoTime, $visMatch)) {
            $currentBaseVis = $visMatch[1] === 'CAVOK' ? 9999 : (int)$visMatch[1];
        }

        while ($oi < $markerCount) {
            $label = $markers[0][$oi][0];
            $startPos = $markers[0][$oi][1];
            $nextStartPos = ($oi + 1 < $markerCount) ? $markers[0][$oi + 1][1] : strlen($bodyForOrder);
            
            if (in_array($label, ["PROB30", "PROB40"]) && ($oi + 1 < $markerCount) && $markers[0][$oi + 1][0] == "TEMPO") {
                $label .= " TEMPO";
                $nextStartPos = ($oi + 2 < $markerCount) ? $markers[0][$oi + 2][1] : strlen($bodyForOrder);
                $oi += 2;
            } else {
                $oi += 1;
            }
            
            $groupText = trim(substr($bodyForOrder, $startPos, $nextStartPos - $startPos));

            if (preg_match('/\b(\d{2})(\d{2})\/(\d{2})(\d{2})\b/', $groupText, $tm)) {
                $gStartDay = (int)$tm[1];
                $gStartHour = (int)$tm[2];
                $gEndDay = (int)$tm[3];
                $gEndHour = (int)$tm[4];

                $gStartTotal = $getContinuousHour($gStartDay, $gStartHour, $issueDay);
                $gEndTotal = $getContinuousHour($gEndDay, $gEndHour, $issueDay);
                
                if ($gStartDay === $gEndDay && $gStartHour === $gEndHour) {
                    $gEndTotal += 24;
                }
                
                $gDuration = $gEndTotal - $gStartTotal;
                $periodText = "{$tm[1]}{$tm[2]}/{$tm[3]}{$tm[4]}";

                if (str_contains($label, 'TEMPO')) {
                    if ($gDuration > 2 || $gDuration <= 0) {
                        $addResult("$label $periodText", "Batas Durasi TEMPO", "Periode TEMPO maksimal 2 jam (terdeteksi: $gDuration jam).", false);
                    } else {
                        $addResult("$label $periodText", "Batas Durasi TEMPO", "Periode durasi valid.", true);
                    }
                }
                
                if (str_contains($label, 'BECMG')) {
                    if ($gDuration > 4 || $gDuration < 1) {
                        $addResult("$label $periodText", "Batas Durasi BECMG", "Durasi BECMG maksimal 4 jam (terdeteksi: $gDuration jam).", false);
                    } else {
                        $addResult("$label $periodText", "Batas Durasi BECMG", "Periode durasi valid.", true);
                    }
                }

                $isOverlapping = false;
                foreach ($parsedGroupsForOverlap as $pg) {
                    if ($gStartTotal < $pg['end'] && $gEndTotal > $pg['start']) {
                        $isOverlapping = true;
                        $addResult(
                            "$label $periodText", 
                            "Cek Waktu Beririsan (Overlap)", 
                            "Waktu ini beririsan/bertabrakan dengan perubahan sebelumnya: ({$pg['label']} {$pg['period']}).", 
                            false
                        );
                    }
                }
                
                if (!$isOverlapping) {
                    $addResult("$label $periodText", "Cek Waktu Beririsan", "Tidak terdapat irisan waktu.", true);
                }

                $parsedGroupsForOverlap[] = [
                    'label' => $label,
                    'period' => $periodText,
                    'start' => $gStartTotal,
                    'end' => $gEndTotal
                ];
            }

            $groupTextNoTime = preg_replace('/\b\d{4}\/\d{4}\b/', '', $groupText);
            
            if (preg_match('/\b(\d{4}|CAVOK)\b/', $groupTextNoTime, $visMatch)) {
                $groupVis = $visMatch[1] === 'CAVOK' ? 9999 : (int)$visMatch[1];
                
                if ($currentBaseVis !== null) {
                    $getVisClass = function($v) {
                        if ($v <= 799) return 1;
                        if ($v <= 1499) return 2;
                        if ($v <= 2999) return 3;
                        if ($v <= 5000) return 4;
                        return 5;
                    };

                    $prevClass = $getVisClass($currentBaseVis);
                    $newClass = $getVisClass($groupVis);

                    if ($prevClass === $newClass) {
                        $addResult(
                            "$label Visibility {$visMatch[1]}", 
                            "Perubahan Threshold Visibility", 
                            "Visibility berubah dari {$currentBaseVis}m ke {$groupVis}m tapi masih berada dalam satu kelas yang sama. Perubahan visibility TAF wajib melewati batas threshold kelas.", 
                            false
                        );
                    } else {
                        $addResult(
                            "$label Visibility {$visMatch[1]}", 
                            "Perubahan Threshold Visibility", 
                            "Visibility berubah kelas (dari $currentBaseVis ke {$groupVis}). Valid.", 
                            true
                        );
                    }
                }

                if ($label === 'BECMG' || str_contains($label, 'FM')) {
                    $currentBaseVis = $groupVis;
                }
            }
            
            $orderChunks[] = [$label, $groupText];
        }

        $ranks = ["wind" => 0, "visibility" => 1, "weather" => 2, "cloud" => 3, "wind_shear" => 4];
        
        foreach ($orderChunks as $chunk) {
            $groupLabel = $chunk[0];
            $groupText = $chunk[1];
            
            $elementText = preg_replace('/^\s*(?:(?:PROB30|PROB40)\s+TEMPO|BECMG|TEMPO|INTER|PROB30|PROB40|FM\d{6})\b/', '', $groupText);
            $elementText = preg_replace('/\b\d{4}\/\d{4}\b/', '', $elementText);
            
            $found = [];
            
            if (preg_match_all('/\b(?:VRB|\d{3})\d{2,3}(?:G\d{2,3})?KT\b/', $elementText, $m, PREG_OFFSET_CAPTURE)) {
                foreach($m[0] as $match) $found[] = ["wind", $match[1], $match[0]];
            }
            if (preg_match_all('/\b(?:CAVOK|\d{4}|VV(?:\d{3}|[\/]{3}))\b/', $elementText, $m, PREG_OFFSET_CAPTURE)) {
                foreach($m[0] as $match) $found[] = ["visibility", $match[1], $match[0]];
            }
            if (preg_match_all('/\b(?:NSW|[+-]?(?:(?:MI|PR|BC|DR|BL|SH|TS|FZ))?(?:DZ|RA|SN|SG|IC|PL|GR|GS|UP|FG|BR|SA|DU|HZ|FU|VA|SQ|PO|FC|SS|DS))\b/', $elementText, $m, PREG_OFFSET_CAPTURE)) {
                foreach($m[0] as $match) $found[] = ["weather", $match[1], $match[0]];
            }
            if (preg_match_all('/\b(?:FEW|SCT|BKN|OVC)\d{3}(?:CB|TCU)?\b|\b(?:NSC|NCD|SKC)\b/', $elementText, $m, PREG_OFFSET_CAPTURE)) {
                foreach($m[0] as $match) $found[] = ["cloud", $match[1], $match[0]];
            }
            if (preg_match_all('/\bWS\d{3}\/\d{3}\d{2,3}KT\b/', $elementText, $m, PREG_OFFSET_CAPTURE)) {
                foreach($m[0] as $match) $found[] = ["wind_shear", $match[1], $match[0]];
            }

            usort($found, function($a, $b) { return $a[1] <=> $b[1]; });
            
            $rankSequence = array_map(function($item) use ($ranks) { return $ranks[$item[0]]; }, $found);
            $sortedRankSequence = $rankSequence;
            sort($sortedRankSequence);
            
            $badOrder = $rankSequence !== $sortedRankSequence;
            $tokensOutput = implode(" ", array_column($found, 2));

            if ($badOrder) {
                $addResult($groupLabel, "Urutan parameter", "Urutan tidak sesuai (Angin → Vis → Cuaca → Awan). Ditemukan: $tokensOutput.", false);
            } elseif (!empty($found)) {
                $addResult($groupLabel, "Urutan parameter", "Urutan parameter sesuai. Ditemukan: $tokensOutput.", true);
            }

            if (preg_match_all('/\b(?:FEW|SCT|BKN|OVC)(\d{3})(?:CB|TCU)?\b/', $elementText, $cloudMatches)) {
                $heights = array_map('intval', $cloudMatches[1]);
                $sortedHeights = $heights;
                sort($sortedHeights);
                if (count($heights) > 1 && $heights !== $sortedHeights) {
                    $addResult($groupLabel, "Urutan lapisan awan", "Lapisan awan tidak berurutan dari dasar terendah ke tertinggi.", false);
                } else {
                    $addResult($groupLabel, "Urutan lapisan awan", "Lapisan awan berurutan dari dasar terendah ke tertinggi.", true);
                }
            }
        }

        // PERBAIKAN: ATURAN CUACA SIGNIFIKAN & NSW (Hanya yang diizinkan senior)
        $getWeatherToken = function($text) {
            if (preg_match_all('/(?<![A-Z0-9])([+-]?(?:TSRA|SHRA|RA|SH|SN|DZ|GR|GS|TS))(?![A-Z0-9])/', $text, $matches)) {
                return $matches[1][0];
            }
            return null;
        };

        // Daftar cuaca yg kalau hilang WAJIB diganti NSW (HANYA INI SESUAI ARAHAN: -TSRA, TSRA, +TSRA, RA, +RA, SH, SN)
        $significantWeatherList = ['-TSRA', 'TSRA', '+TSRA', 'RA', '+RA', 'SH', 'SN'];

        $previousWeather = $getWeatherToken($orderChunks[0][1]);
        $previousHadSignificant = in_array($previousWeather, $significantWeatherList);

        for ($i = 1; $i < count($orderChunks); $i++) {
            $groupLabel = $orderChunks[$i][0];
            $groupText = $orderChunks[$i][1];

            if (!preg_match('/^(?:TEMPO|BECMG|PROB)/', $groupLabel)) {
                $currentWeather = $getWeatherToken($groupText);
                if ($currentWeather) {
                    $previousHadSignificant = in_array($currentWeather, $significantWeatherList);
                }
                continue;
            }

            $currentWeather = $getWeatherToken($groupText);
            $hasNsw = (bool) preg_match('/\bNSW\b/', $groupText);

            // Cek jika ada -RA (Perubahan tidak signifikan)
            if ($currentWeather === '-RA') {
                $addResult("$groupLabel $currentWeather", "Signifikansi Cuaca", "Perubahan cuaca menjadi -RA tidak signifikan. Hapus -RA dari kelompok perubahan ini.", false);
            }

            // ATURAN NSW (Hanya memvalidasi jika cuaca sebelumnya ada di $significantWeatherList)
            if ($currentWeather && $hasNsw) {
                $addResult($groupLabel, "Aturan NSW", "NSW tidak boleh ditulis jika cuaca ($currentWeather) masih diprakirakan.", false);
            } elseif ($previousHadSignificant && !$currentWeather && !$hasNsw) {
                $addResult($groupLabel, "Aturan NSW", "Sebelumnya diprakirakan cuaca signifikan ($previousWeather). Jika cuaca ini berakhir, WAJIB cantumkan NSW.", false);
            } elseif ($previousHadSignificant && $hasNsw) {
                $addResult($groupLabel, "Aturan NSW", "NSW digunakan dengan benar untuk menandai berakhirnya cuaca signifikan ($previousWeather).", true);
            } elseif (!$previousHadSignificant && $hasNsw) {
                // TAMBAHAN: Salahkan jika NSW digunakan untuk mengakhiri cuaca yang BUKAN signifikan (seperti -RA)
                $addResult($groupLabel, "Aturan NSW", "NSW hanya digunakan untuk menandai berakhirnya cuaca signifikan tertentu (-TSRA, TSRA, +TSRA, RA, +RA, SH, SN). Penggunaan NSW di sini tidak tepat.", false);
            }

            if ($currentWeather) {
                $previousHadSignificant = in_array($currentWeather, $significantWeatherList);
            } elseif ($hasNsw) {
                $previousHadSignificant = false;
            }
        }

        // Mandatory Elements in GENERAL Group
        $generalTextClean = preg_replace('/\b\d{6}Z\b/', '', $orderChunks[0][1]);
        $generalTextClean = preg_replace('/\b\d{4}\/\d{4}\b/', '', $generalTextClean);
        
        $windOk = (bool) preg_match('/\b(?:VRB|\d{3})\d{2,3}(?:G\d{2,3})?KT\b/', $generalTextClean, $windMatch);
        $addResult($windMatch[0] ?? "Angin", "Kelompok Angin Permukaan", $windOk ? "Format Angin Valid." : "Kelompok angin tidak ditemukan.", $windOk);

        $visOk = (bool) preg_match('/\b(?:\d{4}|CAVOK)\b/', $generalTextClean, $visMatch);
        $addResult($visMatch[0] ?? "Visibility", "Kelompok Visibility", $visOk ? "Format Visibility Valid." : "Visibility/CAVOK tidak ditemukan atau digit salah.", $visOk);

        $cloudOk = (bool) preg_match('/\b(?:CAVOK|FEW|SCT|BKN|OVC)\d{3}(?:CB|TCU)?\b|\b(?:NSC|NCD|SKC)\b/', $generalTextClean, $cloudMatch);
        $addResult($cloudMatch[0] ?? "Cloud", "Kelompok Awan", $cloudOk ? "Kelompok awan/clear-sky dikenali." : "Kelompok awan/clear-sky tidak ditemukan.", $cloudOk);

        // PROB Check
        if (preg_match_all('/\bPROB(?!30\b|40\b)\d{2}\b/', $clean, $badProbs)) {
            $addResult(implode(" ", array_unique($badProbs[0])), "Sandi PROB", "Nilai probabilitas selain PROB30/PROB40 ditemukan.", false);
        } elseif (preg_match_all('/\bPROB(?:30|40)\b/', $clean, $goodProbs)) {
            $addResult(implode(" / ", array_unique($goodProbs[0])), "Sandi PROB", "Nilai probabilitas valid.", true);
        }

        // Closing Marker
        if (str_ends_with(trim($clean), '=')) {
            $addResult("=", "Tanda Penutup", "Terdapat tanda (=) di akhir berita.", true);
        } else {
            $addResult("=", "Tanda Penutup", "Tanda (=) tidak ditemukan di akhir berita.", false);
        }

        $this->qcResult = [
            'results' => $results,
            'errors' => $errors,
            'is_valid' => $errors === 0
        ];
    }

    public function clearQualityControl(): void
    {
        $this->rawTaf = '';
        $this->qcResult = [];
    }

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function getRealtimeDataProperty(): array
    {
        $date = Carbon::parse($this->selectedDate);
        $cacheKey = "taf_realtime_v3_{$this->icao}_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($date) {
            $api = app(BmkgAviationApiService::class);
            $verifier = app(TafVerifierService::class);

            $start = $date->copy()->subDay()->startOfDay(); 
            $end = $date->copy()->endOfDay();

            $rawData = $api->collectData($this->icao, $start, $end);
            $tafs = $rawData['tafs'];
            $observations = $rawData['observations'];

            $rows = [];
            $now = now();
            $maxHour = $date->isToday() ? $now->hour : 23;

            $totalEvaluated = 0;
            $passedSlots = 0;
            $failedSlots = 0;
            $paramStats = [
                'wind_dir' => ['pass' => 0, 'total' => 0, 'label' => 'Arah Angin'],
                'wind_speed' => ['pass' => 0, 'total' => 0, 'label' => 'Kecepatan Angin'],
                'gust' => ['pass' => 0, 'total' => 0, 'label' => 'Gust'],
                'visibility' => ['pass' => 0, 'total' => 0, 'label' => 'Visibility'],
                'precip' => ['pass' => 0, 'total' => 0, 'label' => 'Cuaca'],
                'cloud' => ['pass' => 0, 'total' => 0, 'label' => 'Awan'],
                'ceiling' => ['pass' => 0, 'total' => 0, 'label' => 'Ceiling'],
            ];

            for ($hour = 0; $hour <= $maxHour; $hour++) {
                foreach ([0, 30] as $minute) {
                    $targetTime = $date->copy()->setTime($hour, $minute);
                    if ($targetTime > $now && $date->isToday()) {
                        break;
                    }

                    // PERBAIKAN BUG: Kumpulkan semua kandidat yang aktif di targetTime
                    $validTafCandidates = [];
                    foreach ($tafs as $t) {
                        $vStart = Carbon::parse($t->validStart ?? $t->valid_start);
                        $vEnd = Carbon::parse($t->validEnd ?? $t->valid_end);
                        if ($vStart <= $targetTime && $targetTime <= $vEnd) {
                            $validTafCandidates[] = $t;
                        }
                    }

                    $activeTaf = null;
                    if (!empty($validTafCandidates)) {
                        $bestTaf = null;
                        $maxScore = -INF;
                        
                        foreach ($validTafCandidates as $c) {
                            $rawText = $c->raw ?? '';
                            $issueScore = 0;
                            
                            // Tarik waktu rilis dari TAF Raw 
                            if (preg_match('/(?:FT[A-Z0-9]{4}\s+\w{4}\s+(\d{2})(\d{2})(\d{2})|TAF(?:\s+(?:AMD|COR))?\s+\w{4}\s+(\d{2})(\d{2})(\d{2})Z)/', $rawText, $m)) {
                                $day = (int)($m[1] ?: $m[4]);
                                $hourNum = (int)($m[2] ?: $m[5]);
                                $minNum = (int)($m[3] ?: $m[6]);
                                
                                $diffDay = $day - $targetTime->day;
                                if ($diffDay > 15) $diffDay -= 30; // Jika pindah bulan
                                if ($diffDay < -15) $diffDay += 30; // Jika pindah bulan
                                
                                $issueScore = ($diffDay * 1440) + ($hourNum * 60) + $minNum;
                            } else {
                                $issueScore = -999999;
                            }

                            if (str_contains($rawText, 'COR')) $issueScore += 2;
                            if (str_contains($rawText, 'AMD')) $issueScore += 1;

                            if ($issueScore > $maxScore) {
                                $maxScore = $issueScore;
                                $bestTaf = $c;
                            }
                        }
                        $activeTaf = $bestTaf;
                    }

                    $activeObs = null;
                    foreach ($observations as $obs) {
                        $obsAt = Carbon::parse($obs->at);
                        if (abs($obsAt->getTimestamp() - $targetTime->getTimestamp()) <= 900) {
                            $activeObs = $obs;
                            break;
                        }
                    }

                    $scores = null;
                    $groupKind = '-';
                    $slotPassed = false;

                    if ($activeTaf && $activeObs) {
                        $match = $verifier->scoreForTime($activeTaf, $activeObs);
                        $expectedGroup = $match['group'];

                        $groupKind = is_object($expectedGroup) ? $expectedGroup->kind : ($expectedGroup['kind'] ?? '-');
                        $scores = $match['scores'];

                        $totalEvaluated++;
                        $sumScore = array_sum($scores);
                        if ($sumScore === 7) {
                            $passedSlots++;
                            $slotPassed = true;
                        } else {
                            $failedSlots++;
                        }

                        foreach ($scores as $key => $val) {
                            if (isset($paramStats[$key])) {
                                $paramStats[$key]['total']++;
                                if ($val === 1) {
                                    $paramStats[$key]['pass']++;
                                }
                            }
                        }
                    }

                    $rows[] = [
                        'time' => $targetTime->format('H:i') . ' UTC',
                        'taf_raw' => $activeTaf?->raw ?? 'Data TAF Belum Tersedia',
                        'metar_raw' => $activeObs?->raw ?? 'Data METAR Belum Tersedia',
                        'group' => $groupKind,
                        'scores' => $scores,
                        'passed' => $slotPassed,
                    ];
                }
            }

            $overallAccuracy = $totalEvaluated > 0 ? round(($passedSlots / $totalEvaluated) * 100, 1) : 0;

            return [
                'rows' => array_reverse($rows),
                'summary' => [
                    'total' => $totalEvaluated,
                    'passed' => $passedSlots,
                    'failed' => $failedSlots,
                    'accuracy' => $overallAccuracy,
                    'paramStats' => $paramStats,
                ],
            ];
        });
    }

    public function refreshData(): void
    {
        $cacheKey = "taf_realtime_v3_{$this->icao}_" . Carbon::parse($this->selectedDate)->format('Y-m-d');
        Cache::forget($cacheKey);
    }

    public function exportExcelAction(): Action
    {
        return Action::make('exportExcel')
            ->label('Download Excel')
            ->icon('heroicon-m-arrow-down-tray')
            ->color('success')
            ->size('sm')
            ->modalHeading('Unduh Laporan Verifikasi TAF')
            ->modalDescription('Pilih bulan dan tahun laporan yang ingin Anda unduh ke dalam format Excel.')
            ->modalSubmitActionLabel('Unduh Laporan')
            ->form([
                Select::make('month')
                    ->label('Bulan Laporan')
                    ->options([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default(now()->format('m'))
                    ->required(),

                Select::make('year')
                    ->label('Tahun Laporan')
                    ->options(function () {
                        $currentYear = (int) now()->format('Y');
                        $years = [];
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            $years[$y] = (string) $y;
                        }
                        return $years;
                    })
                    ->default(now()->format('Y'))
                    ->required(),
            ])
            ->action(function (array $data, TafReportExcelService $excelService) {
                set_time_limit(300);

                $date = Carbon::createFromDate((int)$data['year'], (int)$data['month'], 1)->startOfDay();

                try {
                    $api = app(BmkgAviationApiService::class);
                    
                    $start = $date->copy()->subDays(2)->startOfMonth(); 
                    $end = $date->copy()->endOfMonth();

                    $rawData = $api->collectData($this->icao, $start, $end);
                    $tafs = $rawData['tafs'];
                    $observations = $rawData['observations'];

                    $fileName = $excelService->generate($date, $tafs, $observations);
                    $filePath = storage_path('app/public/' . $fileName);

                    if (!file_exists($filePath)) {
                        throw new \Exception("File hasil export Excel tidak ditemukan.");
                    }

                    return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal Mengunduh Laporan')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function viewDetailsAction(): Action
    {
        return Action::make('viewDetails')
            ->modalHeading(fn (array $arguments) => 'Detail Sandi Jam ' . ($arguments['time'] ?? ''))
            ->modalSubmitAction(false) // Sembunyikan tombol submit karena cuma untuk melihat
            ->modalCancelActionLabel('Tutup')
            ->form([
                \Filament\Forms\Components\Textarea::make('taf_raw')
                    ->label('TAF yang Digunakan')
                    ->disabled()
                    ->rows(3),
                \Filament\Forms\Components\Textarea::make('metar_raw')
                    ->label('METAR / SPECI yang Diverifikasi')
                    ->disabled()
                    ->rows(2),
            ])
            ->fillForm(function (array $arguments): array {
                // Cari data berdasarkan jam yang diklik dari cache/property
                $rows = $this->realtimeData['rows'] ?? [];
                $targetTime = $arguments['time'] ?? '';
                
                $tafRaw = 'Tidak ada data TAF';
                $metarRaw = 'Tidak ada observasi (METAR/SPECI)';

                foreach ($rows as $row) {
                    if ($row['time'] === $targetTime) {
                        $tafRaw = $row['taf_raw'] ?? 'Tidak ada data TAF';
                        $metarRaw = $row['metar_raw'] ?? 'Tidak ada observasi (METAR/SPECI)';
                        break;
                    }
                }

                return [
                    'taf_raw' => $tafRaw,
                    'metar_raw' => $metarRaw,
                ];
            });
    }
}