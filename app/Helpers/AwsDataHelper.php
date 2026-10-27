<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class AwsDataHelper
{

    public static function restartAws(): array
    {
        $url = setting('aws_server_url') . '/aws/restart';
        $token = setting('aws_server_token');

        try {
            $response = Http::withToken($token)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'detail' => 'AWS berhasil direstart.'
                ];
            } else {

                return [
                    'status' => 'error',
                    'detail' => 'Gagal merestart AWS.'
                ];
            }
        } catch (\Throwable $e) {
            logger('Gagal merestart AWS: ' . $e->getMessage());

            return [
                'status' => 'error',
                'detail' => 'Gagal merestart AWS.',
            ];
        }
    }
}
