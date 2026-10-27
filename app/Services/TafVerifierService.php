<?php

namespace App\Services;

use Carbon\Carbon;

class TafCondition
{
    public ?int $windDir = null;
    public ?int $windSpeed = null;
    public ?bool $gust = false;
    public ?int $visibility = null;
    public ?string $precipCode = 'NONE';
    public ?string $cloud = null;
    public ?int $ceilingFt = null;

    public function merged(TafCondition $newer): TafCondition
    {
        $out = clone $this;
        if ($newer->windDir !== null) $out->windDir = $newer->windDir;
        if ($newer->windSpeed !== null) $out->windSpeed = $newer->windSpeed;
        if ($newer->gust !== null) $out->gust = $newer->gust;
        if ($newer->visibility !== null) $out->visibility = $newer->visibility;
        if ($newer->precipCode !== null && $newer->precipCode !== '') $out->precipCode = $newer->precipCode;
        if ($newer->cloud !== null && $newer->cloud !== '') $out->cloud = $newer->cloud;
        if ($newer->ceilingFt !== null) $out->ceilingFt = $newer->ceilingFt;

        return $out;
    }

    public static function from(mixed $data): TafCondition
    {
        if ($data instanceof TafCondition) {
            return $data;
        }

        $c = new TafCondition();
        if (is_array($data)) {
            $c->windDir = $data['wind_dir'] ?? $data['windDir'] ?? null;
            $c->windSpeed = $data['wind_speed'] ?? $data['windSpeed'] ?? null;
            $c->gust = (bool)($data['gust'] ?? false);
            $c->visibility = $data['visibility'] ?? null;
            $c->precipCode = $data['precip_code'] ?? $data['precipCode'] ?? 'NONE';
            $c->cloud = $data['cloud'] ?? null;
            $c->ceilingFt = $data['ceiling_ft'] ?? $data['ceilingFt'] ?? null;
        } elseif (is_object($data)) {
            $c->windDir = $data->wind_dir ?? $data->windDir ?? null;
            $c->windSpeed = $data->wind_speed ?? $data->windSpeed ?? null;
            $c->gust = (bool)($data->gust ?? $data->gust ?? false);
            $c->visibility = $data->visibility ?? null;
            $c->precipCode = $data->precip_code ?? $data->precipCode ?? 'NONE';
            $c->cloud = $data->cloud ?? null;
            $c->ceilingFt = $data->ceiling_ft ?? $data->ceilingFt ?? null;
        }

        return $c;
    }

    public function toArray(): array
    {
        return [
            'wind_dir' => $this->windDir,
            'wind_speed' => $this->windSpeed,
            'gust' => $this->gust,
            'visibility' => $this->visibility,
            'precip_code' => $this->precipCode,
            'cloud' => $this->cloud,
            'ceiling_ft' => $this->ceilingFt,
        ];
    }
}

class TafVerifierService
{
    public function parseCondition(string $text): TafCondition
    {
        $c = new TafCondition();

        if (preg_match('/\b(VRB|\d{3})(\d{2,3})(G\d{2,3})?KT\b/i', $text, $wind)) {
            $c->windDir = (strtoupper($wind[1]) === 'VRB') ? 999 : (int) $wind[1];
            $c->windSpeed = (int) $wind[2];
            $c->gust = !empty($wind[3]);
        }

        if (preg_match('/\bCAVOK\b/i', $text)) {
            $c->visibility = 9999;
            $c->cloud = 'SKC';
            $c->ceilingFt = 9999;
        } else {
            if (preg_match('/(?:^|\s)(\d{4})(?:\s|$)/', $text, $vis)) {
                $c->visibility = (int) $vis[1];
            }

            if (preg_match_all('/\b(FEW|SCT|BKN|OVC)(\d{3})(?:CB|TCU)?\b/i', $text, $matches, PREG_SET_ORDER)) {
                usort($matches, fn($a, $b) => (int) $a[2] <=> (int) $b[2]);
                $c->cloud = strtoupper($matches[0][1]);
                $c->ceilingFt = ((int) $matches[0][2]) * 100;
            } elseif (preg_match('/\b(NSC|NCD|SKC)\b/i', $text)) {
                $c->cloud = 'SKC';
                $c->ceilingFt = 9999;
            }
        }

        if (preg_match_all('/(?<![A-Z0-9])([+-]?(?:SH|TS)?(?:RA|DZ|SN|SG|PL|GR|GS))(?![A-Z0-9])/i', $text, $wxMatches)) {
            $rainTokens = array_values(array_filter($wxMatches[1], fn($w) => str_ends_with(strtoupper($w), 'RA')));
            if (!empty($rainTokens)) {
                $c->precipCode = strtoupper($rainTokens[0]);
            } else {
                $c->precipCode = 'NONE';
            }
        } elseif (preg_match('/\bNSW\b/i', $text)) {
            $c->precipCode = 'NONE';
        } else {
            $c->precipCode = 'NONE';
        }

        return $c;
    }

    public function visibilityClass(?int $v): ?int
    {
        if ($v === null) return null;
        if ($v < 800) return 1;
        if ($v < 1500) return 2;
        if ($v < 3000) return 3;
        if ($v <= 5000) return 4;
        return 5;
    }

    public function calculateScore(mixed $expected, mixed $actual, string $rawObs = ''): array
    {
        $exp = TafCondition::from($expected);
        $act = TafCondition::from($actual);

        $windDirScore = $this->windDirOk($exp, $act, $rawObs);
        $windSpeedScore = ($exp->windSpeed !== null && $act->windSpeed !== null && abs($exp->windSpeed - $act->windSpeed) <= 10) ? 1 : 0;
        $gustScore = (($exp->gust ?? false) === ($act->gust ?? false)) ? 1 : 0;
        $visScore = ($exp->visibility !== null && $act->visibility !== null && $this->visibilityClass($exp->visibility) === $this->visibilityClass($act->visibility)) ? 1 : 0;
        $precipScore = $this->precipOk($exp, $act);
        $cloudScore = $this->cloudOk($exp, $act);

        if ($exp->ceilingFt === null || $act->ceilingFt === null) {
            $ceilingScore = 0;
        } elseif ($exp->ceilingFt < 1000) {
            $ceilingScore = (abs($exp->ceilingFt - $act->ceilingFt) <= 100) ? 1 : 0;
        } else {
            $ceilingScore = (abs($exp->ceilingFt - $act->ceilingFt) <= 0.30 * $exp->ceilingFt) ? 1 : 0;
        }

        return [
            'wind_dir' => $windDirScore,
            'wind_speed' => $windSpeedScore,
            'gust' => $gustScore,
            'visibility' => $visScore,
            'precip' => $precipScore,
            'cloud' => $cloudScore,
            'ceiling' => $ceilingScore,
        ];
    }

    public function calculateScoreBecmg(mixed $previous, mixed $target, mixed $actual, string $rawObs = ''): array
    {
        $prev = TafCondition::from($previous);
        $tgt = TafCondition::from($target);
        $act = TafCondition::from($actual);

        $windDirScore = 0;
        if ($prev->windDir !== null && $tgt->windDir !== null && $act->windDir !== null) {
            $windDirScore = ($this->windDirOk($prev, $act, $rawObs) || $this->windDirOk($tgt, $act, $rawObs)) ? 1 : 0;
        }

        $windSpeedScore = $this->betweenNumber($act->windSpeed, $prev->windSpeed, $tgt->windSpeed) ? 1 : 0;
        $gustScore = (($act->gust ?? false) === ($prev->gust ?? false) || ($act->gust ?? false) === ($tgt->gust ?? false)) ? 1 : 0;

        $visScore = ($this->betweenNumber($act->visibility, $prev->visibility, $tgt->visibility) ||
            ($act->visibility !== null && in_array($this->visibilityClass($act->visibility), [
                $this->visibilityClass($prev->visibility),
                $this->visibilityClass($tgt->visibility)
            ]))) ? 1 : 0;

        $precipScore = ($this->precipOk($prev, $act) || $this->precipOk($tgt, $act)) ? 1 : 0;
        $cloudScore = $this->cloudAmountBetween($prev, $tgt, $act);

        if ($act->ceilingFt === null) {
            $ceilingScore = 0;
        } else {
            $prevScore = $this->calculateScore($prev, $act, $rawObs);
            $tgtScore = $this->calculateScore($tgt, $act, $rawObs);

            $ceilingScore = ($this->betweenNumber($act->ceilingFt, $prev->ceilingFt, $tgt->ceilingFt) ||
                $prevScore['ceiling'] || $tgtScore['ceiling']) ? 1 : 0;
        }

        return [
            'wind_dir' => $windDirScore,
            'wind_speed' => $windSpeedScore,
            'gust' => $gustScore,
            'visibility' => $visScore,
            'precip' => $precipScore,
            'cloud' => $cloudScore,
            'ceiling' => $ceilingScore,
        ];
    }

    public function scoreForTime(mixed $taf, mixed $obs): array
    {
        $obsAt = is_array($obs) ? Carbon::parse($obs['at']) : ($obs->at instanceof Carbon ? $obs->at : Carbon::parse($obs->at));
        $rawObs = is_array($obs) ? ($obs['raw'] ?? '') : ($obs->raw ?? '');
        $actCondition = is_array($obs) ? ($obs['condition'] ?? $obs) : ($obs->condition ?? $obs);

        $groups = $this->matchingGroups($taf, $obsAt);
        $group = end($groups);

        $gKind = is_object($group) ? $group->kind : ($group['kind'] ?? 'GENERAL');
        $gEnd = Carbon::parse(is_object($group) ? $group->end : $group['end']);
        $gPrev = is_object($group) ? ($group->previous ?? null) : ($group['previous'] ?? null);
        $gCond = is_object($group) ? $group->condition : $group['condition'];

        $isBecmgActive = ($gKind === 'BECMG' && !empty($gPrev) && $obsAt < $gEnd);

        if ($isBecmgActive) {
            $scores = $this->calculateScoreBecmg($gPrev, $gCond, $actCondition, $rawObs);
        } else {
            $scores = $this->calculateScore($gCond, $actCondition, $rawObs);
        }

        return [
            'group' => $group,
            'scores' => $scores,
        ];
    }

    public function matchingGroups(mixed $taf, Carbon $at): array
    {
        $validStart = Carbon::parse(is_object($taf) ? ($taf->validStart ?? $taf->valid_start) : ($taf['validStart'] ?? $taf['valid_start']));
        $validEnd = Carbon::parse(is_object($taf) ? ($taf->validEnd ?? $taf->valid_end) : ($taf['validEnd'] ?? $taf['valid_end']));
        $groups = is_object($taf) ? ($taf->groups ?? []) : ($taf['groups'] ?? []);

        if ($at < $validStart || $at > $validEnd) {
            return [$groups[0] ?? $this->makeDefaultGroup($validStart, $validEnd)];
        }

        $permanent = array_filter($groups, function ($g) use ($at) {
            $gKind = is_object($g) ? $g->kind : $g['kind'];
            $gStart = Carbon::parse(is_object($g) ? $g->start : $g['start']);
            return in_array($gKind, ['GENERAL', 'BECMG', 'FM']) && $gStart <= $at;
        });

        if (!empty($permanent)) {
            usort($permanent, function ($a, $b) {
                $aStart = Carbon::parse(is_object($a) ? $a->start : $a['start']);
                $bStart = Carbon::parse(is_object($b) ? $b->start : $b['start']);
                return $aStart <=> $bStart;
            });
            $baseGroup = end($permanent);
        } else {
            $baseGroup = $groups[0] ?? $this->makeDefaultGroup($validStart, $validEnd);
        }

        $baseKind = is_object($baseGroup) ? $baseGroup->kind : $baseGroup['kind'];
        $baseEnd = Carbon::parse(is_object($baseGroup) ? $baseGroup->end : $baseGroup['end']);

        if ($baseKind === 'BECMG' && $at < $baseEnd) {
            $base = $baseGroup;
        } else {
            $kind = ($baseKind === 'GENERAL') ? 'GENERAL' : $baseKind;
            $start = Carbon::parse(is_object($baseGroup) ? $baseGroup->start : $baseGroup['start']);
            $condition = is_object($baseGroup) ? $baseGroup->condition : $baseGroup['condition'];
            $previous = is_object($baseGroup) ? ($baseGroup->previous ?? null) : ($baseGroup['previous'] ?? null);

            $base = (object) [
                'kind' => $kind,
                'start' => $start,
                'end' => $validEnd,
                'condition' => $condition,
                'previous' => $previous,
            ];
        }

        $transient = array_filter($groups, function ($g) use ($at) {
            $gKind = is_object($g) ? $g->kind : $g['kind'];
            $gStart = Carbon::parse(is_object($g) ? $g->start : $g['start']);
            $gEnd = Carbon::parse(is_object($g) ? $g->end : $g['end']);

            return in_array($gKind, ['TEMPO', 'PROB30 TEMPO', 'PROB40 TEMPO', 'PROB30', 'PROB40', 'INTER'])
                && ($gStart <= $at && $at < $gEnd);
        });

        if (!empty($transient)) {
            usort($transient, function ($a, $b) {
                $aStart = Carbon::parse(is_object($a) ? $a->start : $a['start']);
                $bStart = Carbon::parse(is_object($b) ? $b->start : $b['start']);
                return $aStart <=> $bStart;
            });
            return [$base, end($transient)];
        }

        return [$base];
    }

    private function windDirOk(TafCondition $expected, TafCondition $actual, string $raw): int
    {
        if ($expected->windDir === null || $actual->windDir === null) return 0;
        if ($expected->windDir === 999 && $actual->windDir === 999) return 1;

        $actSpeed = $actual->windSpeed ?? 999;
        $expSpeed = $expected->windSpeed ?? 999;

        if ($expected->windDir === 999) {
            return ($actSpeed < 10 || str_contains($raw, 'TS') || str_contains($raw, 'CB')) ? 1 : 0;
        }

        if ($actual->windDir === 999) {
            return ($expSpeed < 10 && $actSpeed < 10) ? 1 : 0;
        }

        $delta = abs($expected->windDir - $actual->windDir);
        $delta = min($delta, 360 - $delta);

        if ($delta <= 60) return 1;

        return ($actSpeed < 10) ? 1 : 0;
    }

    private function cloudOk(TafCondition $expected, TafCondition $actual): int
    {
        if ($actual->ceilingFt !== null && $actual->ceilingFt > 4921) {
            return 1;
        }

        if (!$expected->cloud || !$actual->cloud) return 0;

        $low = ['SKC', 'FEW', 'SCT'];
        $high = ['BKN', 'OVC'];

        $eLow = in_array($expected->cloud, $low);
        $aLow = in_array($actual->cloud, $low);
        $eHigh = in_array($expected->cloud, $high);
        $aHigh = in_array($actual->cloud, $high);

        return (($eLow && $aLow) || ($eHigh && $aHigh)) ? 1 : 0;
    }

    private function rainClass(?string $code): string
    {
        if (!$code || $code === 'NONE') return 'NONE';
        $code = strtoupper($code);
        if (str_starts_with($code, '+')) return 'HEAVY';
        if (str_starts_with($code, '-')) return 'LIGHT';
        return 'MODERATE';
    }

    private function precipOk(TafCondition $expected, TafCondition $actual): int
    {
        $e = $this->rainClass($expected->precipCode);
        $a = $this->rainClass($actual->precipCode);

        if ($e === 'HEAVY') return ($a === 'HEAVY') ? 1 : 0;
        if ($e === 'MODERATE') return in_array($a, ['MODERATE', 'HEAVY']) ? 1 : 0;

        return in_array($a, ['NONE', 'LIGHT']) ? 1 : 0;
    }

    private function cloudAmountBetween(TafCondition $previous, TafCondition $target, TafCondition $actual): int
    {
        $order = ['SKC' => 0, 'FEW' => 1, 'SCT' => 2, 'BKN' => 3, 'OVC' => 4];

        if (!isset($order[$previous->cloud], $order[$target->cloud], $order[$actual->cloud])) {
            return 0;
        }

        $lo = min($order[$previous->cloud], $order[$target->cloud]);
        $hi = max($order[$previous->cloud], $order[$target->cloud]);
        $actVal = $order[$actual->cloud];

        return ($actVal >= $lo && $actVal <= $hi) ? 1 : 0;
    }

    private function betweenNumber(?int $value, ?int $a, ?int $b): bool
    {
        if ($value === null || $a === null || $b === null) return false;
        $lo = min($a, $b);
        $hi = max($a, $b);
        return ($value >= $lo && $value <= $hi);
    }

    private function makeDefaultGroup(Carbon $start, Carbon $end): object
    {
        return (object) [
            'kind' => 'GENERAL',
            'start' => $start,
            'end' => $end,
            'condition' => new TafCondition(),
            'previous' => null,
        ];
    }
}