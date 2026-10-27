<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BmkgAviationApiService
{
    protected string $tafUrl = 'https://web-aviation.bmkg.go.id/web/taf.php';
    protected string $metarUrl = 'https://web-aviation.bmkg.go.id/web/metar_speci.php';

    public function collectData(string $icao, Carbon $start, Carbon $end): array
    {
        // PERBAIKAN: Menggunakan $end sebagai anchor untuk mencegah bug pergeseran bulan
        $anchor = $end->copy()->startOfDay();

        $allRawTaf = [];
        $allRawMetar = [];

        // Pecah request per 5 hari agar server BMKG tidak mengembalikan data fallback (hari ini)
        $currentStart = $start->copy()->startOfDay();
        $finalEnd = $end->copy()->endOfDay();

        while ($currentStart->lessThanOrEqualTo($finalEnd)) {
            $currentEnd = $currentStart->copy()->addDays(4)->endOfDay();
            if ($currentEnd->greaterThan($finalEnd)) {
                $currentEnd = $finalEnd->copy();
            }

            // Scraping TAF per chunk
            $rawTafHtml = $this->fetchFromBmkg($this->tafUrl, $icao, $currentStart, $currentEnd);
            $tafMatches = $this->extractTafMessages($rawTafHtml, $icao);
            $allRawTaf = array_merge($allRawTaf, $tafMatches);

            // Scraping METAR/SPECI per chunk
            $rawMetarHtml = $this->fetchFromBmkg($this->metarUrl, $icao, $currentStart, $currentEnd);
            $metarMatches = $this->extractMetarMessages($rawMetarHtml, $icao);
            $allRawMetar = array_merge($allRawMetar, $metarMatches);

            $currentStart = $currentEnd->copy()->addSecond();
        }

        $allRawTaf = array_unique($allRawTaf);
        $allRawMetar = array_unique($allRawMetar);

        $tafs = [];
        foreach ($allRawTaf as $raw) {
            $parsed = TafParserService::parseTaf($raw, $anchor);
            if ($parsed) {
                $tafs[] = $parsed;
            }
        }

        $observations = [];
        foreach ($allRawMetar as $raw) {
            $parsed = TafParserService::parseObservation($raw, $anchor);
            if ($parsed) {
                $observations[] = $parsed;
            }
        }

        Log::info("BMKG Scraping [{$icao}]: Found " . count($tafs) . " TAFs and " . count($observations) . " METARs.");

        return [
            'tafs' => $tafs,
            'observations' => $observations,
        ];
    }

    protected function fetchFromBmkg(string $url, string $icao, Carbon $start, Carbon $end): string
    {
        $get = Http::withoutVerifying()
            ->timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])
            ->get($url);

        preg_match('/name="_token"\s+value="([^"]+)"/i', $get->body(), $matches);
        $token = $matches[1] ?? '';

        $cookies = [];
        foreach ($get->cookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        $host = parse_url($url, PHP_URL_HOST);

        $post = Http::withoutVerifying()
            ->timeout(60)
            ->withCookies($cookies, $host)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => $url,
            ])
            ->asForm()
            ->post($url, [
                '_token'  => $token,
                'stasiun' => $icao,
                'from'    => $start->format('Y-m-d\TH:i'),
                'to'      => $end->format('Y-m-d\TH:i'),
            ]);

        return $post->body();
    }

    protected function extractTafMessages(string $html, string $icao): array
    {
        $plain = strip_tags(str_replace(['<br>', '<br/>', '<br />', '&nbsp;'], ["\n", "\n", "\n", " "], $html));
        preg_match_all('/(?:FTID40\s+'.$icao.'.*?=|(?:TAF(?:\s+(?:AMD|COR))?\s+'.$icao.'.*?=))/si', $plain, $matches);
        return array_map('trim', $matches[0] ?? []);
    }

    protected function extractMetarMessages(string $html, string $icao): array
    {
        $plain = strip_tags(str_replace(['<br>', '<br/>', '<br />', '&nbsp;'], ["\n", "\n", "\n", " "], $html));
        preg_match_all('/((?:METAR|SPECI)(?:\s+COR)?\s+'.$icao.'.*?=)/si', $plain, $matches);
        return array_map('trim', $matches[0] ?? []);
    }
}