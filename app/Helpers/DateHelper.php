<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{

    public static function getNearestSentHour(): Carbon
    {
        $now = now();
        $minute = $now->minute;

        if ($minute >= 10 && $minute <= 50) {
            return $now->copy()->minute(0)->second(0);
        } else {
            return $minute < 10
                ? $now->copy()->minute(0)->second(0)
                : $now->copy()->addHour()->minute(0)->second(0);
        }
    }

    public static function getPreviousDayStartHourIsoTimestamp(): string
    {
        return now()->subDay()->startOfDay()->format('Y-m-d\TH:i:s');
    }

    public static function getCurrentDayEndHourIsoTimestamp(): string
    {
        return now()->endOfDay()->format('Y-m-d\TH:i:s');
    }

    public static function getPreviousHourIsoTimestamp(): string
    {
        return now()->subHour()->startOfHour()->format('Y-m-d\TH:i:s');
    }

    public static function getCurrentHourIsoTimestamp(): string
    {
        return now()->startOfHour()->format('Y-m-d\TH:i:s');
    }

    public static function getNextHourIsoTimestamp(): string
    {
        return now()->addHour()->startOfHour()->format('Y-m-d\TH:i:s');
    }

    public static function formatDataTimestamp(string $dataTimestamp): string
    {
        $carbonDate = Carbon::parse($dataTimestamp);
        return $carbonDate->format('YmdH');
    }

    public static function getPreviousDateString(): string
    {
        return now()->subDay()->format('Y-m-d');
    }
}
