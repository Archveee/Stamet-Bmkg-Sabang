<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;
use Carbon\Carbon;

class BmkgsatuApiService
{
    protected $baseUrl;
    protected $bearerToken;
    protected $tokenExpiration = 2; #days

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        // Ambil baseUrl dari konfigurasi
        $this->baseUrl = setting('bmkgsatu_base_url');

        // Memeriksa apakah token ada dalam cache/session
        $this->bearerToken = Cache::get('bmkgsatu_bearer_token');
    }

    /**
     * Menghapus cache token lama & reset variabel lokal
     */
    public function clearTokenCache()
    {
        Cache::forget('bmkgsatu_bearer_token');
        $this->bearerToken = null;
    }

    /**
     * Mendapatkan Bearer Token dengan mengirimkan username dan password (API v21)
     *
     * @param string $username
     * @param string $password
     * @return string|null
     */
    public function getBearerToken(string $username, string $password)
    {
        try {
            $response = Http::acceptJson()
                ->retry(3, 1000)
                ->post("{$this->baseUrl}/api/v21/user/session/login", [
                    'username' => $username,
                    'password' => $password,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Cek berbagai kemungkinan struktur kunci token pada API v21 BMKG
                $this->bearerToken = $data['token']
                    ?? $data['access_token']
                    ?? $data['data']['token']
                    ?? $data['data']['access_token']
                    ?? null;

                if ($this->bearerToken) {
                    Cache::put('bmkgsatu_bearer_token', $this->bearerToken, now()->addDays($this->tokenExpiration)->subHour());
                    return $this->bearerToken;
                }

                logger()->error("Login API v21 BMKG Satu berhasil (200 OK), tetapi kunci token tidak ditemukan dalam JSON response.", [
                    'response' => $data
                ]);
            } else {
                logger()->error("Gagal Login API v21 BMKG Satu. Status: {$response->status()}", [
                    'body' => $response->body()
                ]);
            }
        } catch (\Throwable $e) {
            logger()->error("Exception saat melakukan login ke BMKG Satu: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Mendapatkan header dengan Bearer Token untuk request selanjutnya
     *
     * @param bool $forceRefresh
     * @return array
     */
    public function getHeaders(bool $forceRefresh = false)
    {
        if ($forceRefresh) {
            $this->clearTokenCache();
        }

        // Jika token belum ada atau expired, dapatkan token lagi
        if (!$this->bearerToken) {
            $username = setting('bmkgsatu_username');
            $password = setting('bmkgsatu_password');

            if (empty($username) || empty($password)) {
                throw new \Exception('Username atau Password BMKG Satu belum diatur di setting.');
            }

            // Coba untuk mendapatkan token
            $this->bearerToken = $this->getBearerToken($username, $password);

            // Jika token tetap tidak didapatkan, lempar exception
            if (!$this->bearerToken) {
                throw new \Exception('Gagal mendapatkan Bearer Token.');
            }
        }

        return [
            'Authorization' => 'Bearer ' . $this->bearerToken,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Helper Wrapper: Mengeksekusi Request dengan Auto-Retry saat Token Expired (401/403)
     * Mengembalikan objek Response HTTP Laravel.
     *
     * @param callable $requestCallback
     * @return \Illuminate\Http\Client\Response
     */
    protected function executeWithTokenRetry(callable $requestCallback)
    {
        // Panggilan Pertama
        $response = $requestCallback($this->getHeaders());

        // Jika merespon 401 Unauthorized atau 403 Forbidden, coba refresh token 1x
        if (in_array($response->status(), [401, 403])) {
            logger()->warning("Status {$response->status()} diterima dari BMKG Satu. Membakar cache token lama dan mencoba login ulang...");

            // Re-login & Ambil Header Baru
            $freshHeaders = $this->getHeaders(true);

            // Coba eksekusi request ulang
            $response = $requestCallback($freshHeaders);
        }

        return $response;
    }

    /**
     * Sanitasi dan formatting payload Sinoptik sesuai standar API v21 BMKG Satu
     */
    protected function sanitizeSinoptikPayload(array $payload): array
    {
        $sanitized = [];

        foreach ($payload as $key => $value) {
            // Ubah string kosong ("") menjadi null
            if ($value === '') {
                $sanitized[$key] = null;
                continue;
            }

            $sanitized[$key] = $value;
        }

        // Format data_timestamp wajib berbentuk ISO 8601 UTC (cth: 2026-07-27T08:00:00Z)
        if (!empty($sanitized['data_timestamp'])) {
            $sanitized['data_timestamp'] = Carbon::parse($sanitized['data_timestamp'])->format('Y-m-d\TH:i:s\Z');
        }

        return $sanitized;
    }

    /**
     * Mendapatkan daftar observer berdasarkan station_id
     */
    public function getObservers(int $stationId)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->get("{$this->baseUrl}/db/bmkgsatu/@aggregation_query", [
                'type_name' => 'Observer',
                'station_id' => $stationId,
                '_metadata' => 'observer_id,observer_nip,observer_name,observer_telephone,observer_is_active',
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new RequestException($response);
    }

    /**
     * Mendapatkan data informasi BMKG stasiun berdasarkan URL
     */
    public function getStationInfo(string $stationUrl)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/db/bmkgsatu/{$stationUrl}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new RequestException($response);
    }

    /**
     * Mendapatkan data GTSMessage berdasarkan filter tertentu.
     */
    public function getGtsMessages(int $typeMessage, string $startTime, string $endTime)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->get("{$this->baseUrl}/db/bmkgsatu/@search", [
                'type_name' => 'GTSMessage',
                '_metadata' => 'type_message,timestamp_data,timestamp_sent_data,station_wmo_id,sandi_gts,ttaaii,cccc,need_ftp',
                'type_message' => $typeMessage,
                'station_wmo_id' => setting('station_wmo_id'),
                'timestamp_data__gte' => $startTime,
                'timestamp_data__lte' => $endTime,
                '_size' => 1000,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new RequestException($response);
    }

    /**
     * Mendapatkan data monitoring sinoptik dari endpoint yang diberikan.
     */
    public function getMonitoringSinoptikData(string $periode)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->get("{$this->baseUrl}/db/bmkgsatu/@monitoring_data", [
                'periode' => $periode,
                'resource' => 2,
                'region' => 1,
                'station_id' => "undefined,1",
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new RequestException($response);
    }

    /**
     * Mendapatkan data monitoring GTS berdasarkan parameter yang diberikan.
     */
    public function getMonitoringGTS(string $periode, int $typeMessage, int $region, int $province, string $stationIds)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->get("{$this->baseUrl}/db/bmkgsatu/@monitoring_gts", [
                'periode' => $periode,
                'type_message' => $typeMessage,
                'region' => $region,
                'province' => $province,
                'station_id' => $stationIds,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new RequestException($response);
    }

   /**
     * Mengirimkan data sinoptik (API v21)
     *
     * @param array $sinoptikData
     * @throws \Illuminate\Http\Client\RequestException
     * @return array
     */
    public function sendSinoptikData(array $sinoptikData)
    {
        // Sanitasi data & ubah timestamp ke ISO 8601 UTC
        $cleanData = $this->sanitizeSinoptikPayload($sinoptikData);

        $stationId = setting('station_bmkgsoft_id', 1);
        $timestampUrl = $cleanData['data_timestamp'];
        $url = "{$this->baseUrl}/api/v21/sinoptik/entry/station/{$stationId}/date/{$timestampUrl}";

        logger()->info(">>> SANITIZED PAYLOAD SINOPTIK V21:", $cleanData);

        $response = $this->executeWithTokenRetry(function ($headers) use ($url, $cleanData) {
            // 1. Utamakan POST untuk entri data baru
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($url, $cleanData);

            // 2. Jika POST gagal karena data ternyata sudah ada di server, perbarui dengan PUT
            if ($response->failed() && (str_contains($response->body(), 'exists') || str_contains($response->body(), 'sudah ada'))) {
                logger("Data sinoptik sudah ada di server, memperbarui via PUT...");
                $response = Http::withHeaders($headers)
                    ->timeout(30)
                    ->put($url, $cleanData);
            }

            return $response;
        });

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json();
    }

    /**
     * Mengupdate data sinoptik via PUT (API v21)
     *
     * @param string $url
     * @param array $sinoptikData
     * @throws \Illuminate\Http\Client\RequestException
     * @return array
     */
    public function updateSinoptikData(string $url, array $sinoptikData)
    {
        $cleanData = $this->sanitizeSinoptikPayload($sinoptikData);

        $response = $this->executeWithTokenRetry(function ($headers) use ($url, $cleanData) {
            return Http::withHeaders($headers)
                ->timeout(30)
                ->put($url, $cleanData);
        });

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json();
    }

    /**
     * Mengirimkan data GTS ke endpoint BMKG Satu (API v21).
     *
     * @param array $gtsMessageData
     * @return \Illuminate\Http\Client\Response
     */
    public function sendGtsMessage(array $gtsMessageData)
    {
        return $this->executeWithTokenRetry(function ($headers) use ($gtsMessageData) {
            return Http::withHeaders($headers)
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v21/gts/entry", $gtsMessageData);
        });
    }

    /**
     * Mengambil detail GTSMessage dari response attribute @name setelah dikirim ke BMKGsatu.
     */
    public function getCreatedGtsMessageDetail(string $createdGtsMessageName)
    {
        $gtsMessageDetailUrl = "{$this->baseUrl}/db/bmkgsatu/1/1/20/1/gts/{$createdGtsMessageName}";

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get($gtsMessageDetailUrl);

            if ($response->successful()) {
                return $response->json();
            }

            logger("Gagal mengambil detail GTSMessage dari BMKGsatu. Status: {$response->status()}");
        } catch (\Throwable $e) {
            logger("Exception saat mengambil detail GTSMessage dari BMKGsatu: " . $e->getMessage());
        }

        return null;
    }
}
