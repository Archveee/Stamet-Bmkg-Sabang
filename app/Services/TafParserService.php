<?php

namespace App\Services;

use Carbon\Carbon;

class TafParserService
{
    /**
     * Parse TAF Raw text menjadi Object TAF terstruktur
     */
    public static function parseTaf(string $raw, ?Carbon $anchor = null): ?object
    {
        $anchor = $anchor ?? now();
        $raw = trim(preg_replace('/\s+/', ' ', $raw));
        if (empty($raw)) return null;

        $verifier = app(TafVerifierService::class);

        $validStart = $anchor->copy()->startOfDay();
        $validEnd = $anchor->copy()->endOfDay();

        if (preg_match('/\b(\d{2})(\d{2})\/(\d{2})(\d{2})\b/', $raw, $m)) {
            $startDay = (int)$m[1];
            $startHour = (int)$m[2];
            $endDay = (int)$m[3];
            $endHour = (int)$m[4];

            $validStart = $anchor->copy()->day($startDay)->setTime($startHour, 0);
            $validEnd = $anchor->copy()->day($endDay)->setTime($endHour, 0);

            if ($endDay < $startDay) {
                $validEnd->addMonth();
            }
            
            // PERBAIKAN BUG DURASI NOL: 
            // TAF misal 1300/1300 artinya berlaku dari jam 00 tgl 13 s.d. jam 00 tgl 14
            if ($startDay === $endDay && $startHour === $endHour) {
                $validEnd->addDay();
            }
        }

        $groups = [];

        $baseCondition = $verifier->parseCondition($raw);
        $groups[] = (object) [
            'kind' => 'GENERAL',
            'start' => $validStart->copy(),
            'end' => $validEnd->copy(),
            'condition' => $baseCondition,
            'previous' => null,
        ];

        $pattern = '/\b(BECMG|TEMPO|FM\d{6}|PROB30\s+TEMPO|PROB40\s+TEMPO|PROB30|PROB40)\s+([\s\S]*?)(?=\b(?:BECMG|TEMPO|FM\d{6}|PROB30|PROB40)\b|$)/i';

        if (preg_match_all($pattern, $raw, $matches, PREG_SET_ORDER)) {
            $prevCondition = $baseCondition;

            foreach ($matches as $match) {
                $kind = strtoupper(trim($match[1]));
                $groupText = $match[2];

                $gStart = $validStart->copy();
                $gEnd = $validEnd->copy();

                if (preg_match('/\b(\d{2})(\d{2})\/(\d{2})(\d{2})\b/', $groupText, $tm)) {
                    $gStart = $anchor->copy()->day((int)$tm[1])->setTime((int)$tm[2], 0);
                    $gEnd = $anchor->copy()->day((int)$tm[3])->setTime((int)$tm[4], 0);
                    if ((int)$tm[3] < (int)$tm[1]) $gEnd->addMonth();
                    
                    // PERBAIKAN BUG DURASI GRUP
                    if ((int)$tm[1] === (int)$tm[3] && (int)$tm[2] === (int)$tm[4]) {
                        $gEnd->addDay();
                    }
                } elseif (preg_match('/^FM(\d{2})(\d{2})(\d{2})/', $kind, $fm)) {
                    $gStart = $anchor->copy()->day((int)$fm[1])->setTime((int)$fm[2], (int)$fm[3]);
                    $kind = 'FM';
                }

                $gCondition = $verifier->parseCondition($groupText);

                $groups[] = (object) [
                    'kind' => $kind,
                    'start' => $gStart,
                    'end' => $gEnd,
                    'condition' => $gCondition,
                    'previous' => $prevCondition,
                ];

                if (in_array($kind, ['GENERAL', 'FM']) || ($kind === 'BECMG' && now() >= $gEnd)) {
                    $prevCondition = $prevCondition->merged($gCondition);
                }
            }
        }

        return (object) [
            'raw' => $raw,
            'validStart' => $validStart,
            'validEnd' => $validEnd,
            'groups' => $groups,
        ];
    }

    /**
     * Parse METAR / SPECI Raw text menjadi Object Observation
     */
    public static function parseMetar(string $raw, ?Carbon $anchor = null): ?object
    {
        $anchor = $anchor ?? now();
        $raw = trim(preg_replace('/\s+/', ' ', $raw));
        if (empty($raw)) return null;

        $verifier = app(TafVerifierService::class);
        $obsAt = $anchor->copy();

        if (preg_match('/\b(\d{2})(\d{2})(\d{2})Z\b/i', $raw, $m)) {
            $day = (int)$m[1];
            $hour = (int)$m[2];
            $min = (int)$m[3];
            $obsAt = $anchor->copy()->day($day)->setTime($hour, $min, 0);
        }

        $condition = $verifier->parseCondition($raw);

        return (object) [
            'raw' => $raw,
            'at' => $obsAt,
            'condition' => $condition,
        ];
    }

    public static function parseObservation(string $raw, ?Carbon $anchor = null): ?object
    {
        return self::parseMetar($raw, $anchor);
    }

    public function parseCondition(string $text): TafCondition
    {
        $verifier = app(TafVerifierService::class);
        return $verifier->parseCondition($text);
    }
}