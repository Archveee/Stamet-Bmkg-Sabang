<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TafReportExcelService
{
    protected TafVerifierService $verifier;

    public function __construct(TafVerifierService $verifier)
    {
        $this->verifier = $verifier;
    }

    protected function getTemplatePath(): string
    {
        $possibleNames = [
            'TemplateVerifTAF.xlsx',
            'Verifikasi_Taf.xlsx',
            'Verifikasi_TAF.xlsx',
            'Template Verif TAF.xlsx',
            'TemplateVerifTAF (1).xlsx',
        ];

        $directories = [
            storage_path('templates'),
            storage_path('app/templates'),
            storage_path('app/public'),
            storage_path('app'),
            storage_path(),
            base_path(),
        ];

        foreach ($directories as $dir) {
            foreach ($possibleNames as $name) {
                $fullPath = $dir . DIRECTORY_SEPARATOR . $name;
                if (file_exists($fullPath)) {
                    return $fullPath;
                }
            }
        }

        throw new \Exception("File template Excel tidak ditemukan. Pastikan file 'TemplateVerifTAF.xlsx' berada di folder 'storage/templates/'.");
    }

    public function generate(Carbon $start, array $tafs, array $observations): string
    {
        $templatePath = $this->getTemplatePath();
        $spreadsheet = IOFactory::load($templatePath);
        $daysInMonth = $start->daysInMonth;

        for ($day = $daysInMonth + 1; $day <= 31; $day++) {
            $sheet = $spreadsheet->getSheetByName((string)$day);
            if ($sheet !== null) {
                $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($sheet));
            }
        }

        $rekapSheet = $spreadsheet->getSheetByName('rekap 1 bulan');
        if ($rekapSheet !== null) {
            $rekapSheet->setCellValue(
                'A2',
                "VERIFIKASI TAFOR BULAN " . strtoupper($start->translatedFormat('F Y')) . " - WITN MAIMUN SALEH SABANG"
            );
        }

        $rekapRow = 7;
        $rekapCols = ['C' => 'wind_dir', 'E' => 'wind_speed', 'G' => 'gust', 'I' => 'visibility', 'K' => 'precip', 'M' => 'cloud', 'O' => 'ceiling'];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $sheet = $spreadsheet->getSheetByName((string)$day);
            if ($sheet !== null) {
                $blockResults = $this->processDaySheet($sheet, $start->year, $start->month, $day, $tafs, $observations);

                if ($rekapSheet !== null) {
                    foreach ([0, 6, 12, 18] as $hour) {
                        $rekapSheet->setCellValue("A{$rekapRow}", $hour === 0 ? $day : null);
                        $rekapSheet->setCellValue("B{$rekapRow}", sprintf('%02d', $hour));
                        $res = $blockResults[$hour] ?? [];

                        foreach ($rekapCols as $col => $scoreKey) {
                            $rekapSheet->setCellValue("{$col}{$rekapRow}", $res[$scoreKey] ?? 0.0);
                        }
                        $rekapRow++;
                    }
                }
            }
        }

        if ($rekapSheet !== null) {
            foreach ($rekapCols as $col => $scoreKey) {
                $sum = 0;
                $count = 0;
                for ($r = 7; $r < $rekapRow; $r++) {
                    $val = $rekapSheet->getCell("{$col}{$r}")->getValue();
                    if (is_numeric($val)) {
                        $sum += (float) $val;
                        $count++;
                    }
                }
                $avg = $count > 0 ? round($sum / $count, 2) : 0.0;
                $rekapSheet->setCellValue("{$col}4", $avg);
            }

            for ($r = $rekapRow; $r <= 131; $r++) {
                foreach (['A', 'B', 'C', 'E', 'G', 'I', 'K', 'M', 'O'] as $col) {
                    $rekapSheet->setCellValue("{$col}{$r}", null);
                }
            }
        }

        $fileName = 'Verifikasi_TAF_' . $start->format('Y_m') . '.xlsx';
        $outputPath = storage_path('app/public/' . $fileName);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($outputPath);

        return $fileName;
    }

    protected function processDaySheet(
        Worksheet $sheet,
        int $year,
        int $month,
        int $day,
        array $tafList,
        array $obsList
    ): array {
        $sheet->setCellValue('B3', 'BULAN : ' . sprintf('%02d', $month));
        $sheet->setCellValue('E3', 'TAHUN : ' . $year);

        $blocks = [
            0  => ['header' => 4,   'first' => 7,   'last' => 31,  'summary' => 60,  'result' => 65],
            6  => ['header' => 70,  'first' => 73,  'last' => 97,  'summary' => 126, 'result' => 131],
            12 => ['header' => 136, 'first' => 139, 'last' => 163, 'summary' => 191, 'result' => 196],
            18 => ['header' => 201, 'first' => 204, 'last' => 228, 'summary' => 257, 'result' => 262],
        ];

        $blockResults = [];

        foreach ($blocks as $startHour => $coords) {
            $validStart = Carbon::createFromDate($year, $month, $day, 'UTC')->setTime($startHour, 0, 0);
            $activeTaf = $this->chooseTafForValidStart($tafList, $validStart);

            $sheet->setCellValue("B{$coords['header']}", $day);
            $sheet->setCellValue("D{$coords['header']}", "Prakiraan TAF " . sprintf('%02d', $startHour) . " UTC");
            if ($activeTaf) {
                $isInvalid = $activeTaf->isInvalid ?? $activeTaf->is_invalid ?? false;
                $tafRawDisplay = $isInvalid
                    ? $activeTaf->raw . "\nTAF TIDAK SESUAI - TIDAK DAPAT DIVERIFIKASI"
                    : $activeTaf->raw;
                $sheet->setCellValue("E{$coords['header']}", $tafRawDisplay);
            } else {
                $sheet->setCellValue("E{$coords['header']}", null);
            }

            $actualRowsData = [];
            $tempoGroups = $activeTaf ? array_filter($activeTaf->groups ?? [], fn($g) => in_array($g->kind ?? $g['kind'] ?? '', ['TEMPO', 'PROB30 TEMPO', 'PROB40 TEMPO', 'PROB30', 'PROB40'])) : [];

            for ($slot = 0; $slot < 25; $slot++) {
                $row = $coords['first'] + $slot;
                $targetTime = $validStart->copy()->addMinutes(30 * $slot);

                $sheet->setCellValue("A{$row}", $slot + 1);
                $sheet->setCellValue("B{$row}", $slot === 0 ? $day : null);
                $sheet->setCellValue("C{$row}", $targetTime->format('H:i'));

                $activeObs = $this->findObservation($obsList, $targetTime);

                $rowData = $this->writeRow($sheet, $row, $activeTaf, $activeObs, $targetTime);
                if ($rowData !== null) {
                    $actualRowsData[] = $rowData;
                }
            }

            $res = $this->writeBlockSummary(
                $sheet,
                $activeTaf,
                $obsList,
                $actualRowsData,
                $tempoGroups,
                $coords['summary'],
                $coords['result']
            );

            $blockResults[$startHour] = $res;
        }

        return $blockResults;
    }

    protected function chooseTafForValidStart(array $tafList, Carbon $validStart): ?object
    {
        $candidates = [];

        foreach ($tafList as $taf) {
            $sRaw = $taf->validStart ?? $taf->valid_start ?? null;
            $eRaw = $taf->validEnd ?? $taf->valid_end ?? null;

            if ($sRaw && $eRaw) {
                $s = Carbon::parse($sRaw);
                $e = Carbon::parse($eRaw);
                if ($s->lessThanOrEqualTo($validStart) && $validStart->lessThan($e)) {
                    $candidates[] = $taf;
                }
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) use ($validStart) {
            $aCor = ($a->isCorrection ?? $a->is_correction ?? false) ? 1 : 0;
            $bCor = ($b->isCorrection ?? $b->is_correction ?? false) ? 1 : 0;
            if ($aCor !== $bCor) return $bCor <=> $aCor;

            $aStart = Carbon::parse($a->validStart ?? $a->valid_start);
            $bStart = Carbon::parse($b->validStart ?? $b->valid_start);
            return abs($aStart->timestamp - $validStart->timestamp) <=> abs($bStart->timestamp - $validStart->timestamp);
        });

        return $candidates[0];
    }

    protected function findObservation(array $obsList, Carbon $targetTime): ?object
    {
        foreach ($obsList as $obs) {
            $obsAt = Carbon::parse($obs->at);
            if (abs($obsAt->timestamp - $targetTime->timestamp) <= 900) {
                return $obs;
            }
        }
        return null;
    }

    protected function writeRow(
        Worksheet $sheet,
        int $row,
        ?object $taf,
        ?object $obs,
        Carbon $targetTime
    ): ?array {
        $isTafValid = $taf && !($taf->isInvalid ?? $taf->is_invalid ?? false);

        if ($isTafValid) {
            $match = $this->verifier->scoreForTime($taf, $obs ?? ['at' => $targetTime, 'raw' => '', 'condition' => []]);
            $group = $match['group'];
            $cond = TafCondition::from($group->condition ?? $group['condition']);

            $sheet->setCellValue("D{$row}", $group->kind ?? $group['kind']);
            $sheet->setCellValue("E{$row}", in_array($group->kind ?? $group['kind'], ['GENERAL', 'FM']) ? null : Carbon::parse($group->start ?? $group['start'])->format('H:i'));
            $sheet->setCellValue("F{$row}", $cond->windDir);
            $sheet->setCellValue("G{$row}", $cond->windSpeed);
            $sheet->setCellValue("H{$row}", $cond->gust ? 'GUST' : 0);
            $sheet->setCellValue("I{$row}", $cond->visibility);
            $sheet->setCellValue("J{$row}", ($cond->precipCode && $cond->precipCode !== 'NONE') ? $cond->precipCode : 0);
            $sheet->setCellValue("K{$row}", $cond->cloud);
            $sheet->setCellValue("L{$row}", $cond->ceilingFt);
        } else {
            $sheet->setCellValue("D{$row}", $taf ? 'TAF TIDAK SESUAI' : 'TAF TIDAK ADA');
            $sheet->setCellValue("E{$row}", $taf ? 'TIDAK DAPAT DIVERIFIKASI' : null);
            for ($c = 6; $c <= 12; $c++) {
                $sheet->setCellValue([$c, $row], null);
            }
        }

        if ($obs) {
            $a = TafCondition::from($obs->condition);
            $sheet->setCellValue("M{$row}", $obs->raw);
            $sheet->setCellValue("N{$row}", $a->windDir);
            $sheet->setCellValue("P{$row}", $a->windSpeed);
            $sheet->setCellValue("R{$row}", $a->gust ? 'GUST' : 0);
            $sheet->setCellValue("T{$row}", $a->visibility);
            $sheet->setCellValue("V{$row}", ($a->precipCode && $a->precipCode !== 'NONE') ? $a->precipCode : 0);
            $sheet->setCellValue("X{$row}", $a->cloud);
            $sheet->setCellValue("Z{$row}", $a->ceilingFt);

            if ($isTafValid) {
                $match = $this->verifier->scoreForTime($taf, $obs);
                $scores = $match['scores'];

                $scoreCols = ['O' => 'wind_dir', 'Q' => 'wind_speed', 'S' => 'gust', 'U' => 'visibility', 'W' => 'precip', 'Y' => 'cloud', 'AA' => 'ceiling'];
                foreach ($scoreCols as $col => $scoreKey) {
                    $sheet->setCellValue("{$col}{$row}", $scores[$scoreKey]);
                }

                return [
                    'kind' => $match['group']->kind ?? 'GENERAL',
                    'scores' => $scores,
                ];
            } else {
                foreach (['O', 'Q', 'S', 'U', 'W', 'Y', 'AA'] as $col) {
                    $sheet->setCellValue("{$col}{$row}", null);
                }
            }
        } else {
            for ($c = 13; $c <= 27; $c++) {
                $sheet->setCellValue([$c, $row], null);
            }
        }

        return null;
    }

    protected function writeBlockSummary(
        Worksheet $sheet,
        ?object $taf,
        array $obsList,
        array $actualRowsData,
        array $tempoGroups,
        int $summaryRow,
        int $resultRow
    ): array {
        $scoreCols = [
            'wind_dir' => 'O',
            'wind_speed' => 'Q',
            'gust' => 'S',
            'visibility' => 'U',
            'precip' => 'W',
            'cloud' => 'Y',
            'ceiling' => 'AA'
        ];

        $keys = array_keys($scoreCols);
        $sums12Jam = array_fill_keys($keys, 0);
        $sumsUmum = array_fill_keys($keys, 0);
        $sumsBecmg = array_fill_keys($keys, 0);

        $obsCount = count($actualRowsData);

        foreach ($actualRowsData as $row) {
            $k = $row['kind'];
            $sc = $row['scores'];

            foreach ($keys as $key) {
                $val = $sc[$key] ?? 0;
                $sums12Jam[$key] += $val;
                if (in_array($k, ['GENERAL', 'FM', 'INTER'])) {
                    $sumsUmum[$key] += $val;
                } elseif ($k === 'BECMG') {
                    $sumsBecmg[$key] += $val;
                }
            }
        }

        foreach ($scoreCols as $key => $col) {
            $sheet->setCellValue("{$col}{$summaryRow}", $sums12Jam[$key]);
            $sheet->setCellValue("{$col}" . ($summaryRow + 1), $sumsUmum[$key]);
            $sheet->setCellValue("{$col}" . ($summaryRow + 2), $sumsBecmg[$key]);
        }

        $tempoExpectedSlots = 0;
        $tempoCorrectByParam = array_fill_keys($keys, 0);

        if ($taf && !empty($tempoGroups)) {
            foreach ($tempoGroups as $g) {
                $gStart = Carbon::parse($g->start ?? $g['start']);
                $gEnd = Carbon::parse($g->end ?? $g['end']);
                $durationSlots = max(0, intdiv($gEnd->timestamp - $gStart->timestamp, 1800));
                $tempoExpectedSlots += $durationSlots;

                for ($s = 0; $s < $durationSlots; $s++) {
                    $ss = $gStart->copy()->addMinutes(30 * $s);
                    $ee = $ss->copy()->addMinutes(30);

                    $slotReports = array_filter($obsList, function ($o) use ($ss, $ee) {
                        $at = Carbon::parse($o->at);
                        return $at->greaterThanOrEqualTo($ss) && $at->lessThan($ee);
                    });

                    if (empty($slotReports)) continue;

                    foreach ($keys as $key) {
                        $slotOk = false;
                        foreach ($slotReports as $o) {
                            $match = $this->verifier->scoreForTime($taf, $o);
                            if (!empty($match['scores'][$key])) {
                                $slotOk = true;
                                break;
                            }
                        }
                        if ($slotOk) {
                            $tempoCorrectByParam[$key]++;
                        }
                    }
                }
            }
        }

        $sheet->setCellValue("M" . ($summaryRow + 3), $tempoExpectedSlots);
        foreach ($scoreCols as $key => $col) {
            $sheet->setCellValue("{$col}" . ($summaryRow + 3), $tempoCorrectByParam[$key]);
        }

        $finalResults = [];

        foreach ($scoreCols as $key => $col) {
            $isInvalid = $taf->isInvalid ?? $taf->is_invalid ?? false;
            if ($obsCount === 0 || !$taf || $isInvalid) {
                $res = 0.0;
            } else {
                $nonTempoCorrect = $sumsUmum[$key] + $sumsBecmg[$key];
                if ($tempoExpectedSlots > 0) {
                    $tc = $tempoCorrectByParam[$key];
                    if ($tc === 0) {
                        $tempoAdj = round(0.30 * $tempoExpectedSlots);
                    } elseif ($tc <= 0.50 * $tempoExpectedSlots) {
                        $tempoAdj = $tempoExpectedSlots;
                    } elseif ($tc < 0.60 * $tempoExpectedSlots) {
                        $tempoAdj = $tc;
                    } else {
                        $tempoAdj = round(0.60 * $tempoExpectedSlots);
                    }
                    $num = $nonTempoCorrect + $tempoAdj;
                } else {
                    $num = $sums12Jam[$key];
                }
                $res = round(min(100.0, max(0.0, ($num / $obsCount) * 100.0)), 2);
            }

            $sheet->setCellValue("{$col}{$resultRow}", $res);
            $finalResults[$key] = $res;
        }

        return $finalResults;
    }
}