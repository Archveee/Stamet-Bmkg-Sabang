<?php

namespace App\Services;

use App\Helpers\GtsMessageHelper;
use Carbon\Carbon;

class GtsMessageVerifier
{
    /**
     * Verifikasi apakah pesan valid berdasarkan jenisnya.
     *
     * @param string $message
     * @return array
     */
    public function verifyMessage(string $message): array
    {
        // TODO
        // sementara belum aktif
        return [];

        $errors = [];
        $type = GtsMessageHelper::extractTypeMessage($message);

        if (!$type) {
            $errors[] = 'Jenis pesan tidak dikenali.';
            return $errors;
        }

        switch ($type) {
            case 'METAR':
                $errors = $this->verifyMetar($message);
                break;
            case 'AAXX':
                $errors = $this->verifySynop($message);
                break;
            case 'DCH': // Data cuaca harian
                $errors = $this->verifyDch($message);
                break;
            case 'SPECI':
                $errors = $this->verifySpeci($message);
                break;
            case 'TAF':
                $errors = $this->verifyTaf($message);
                break;
            case 'WXREV':
                $errors = $this->verifyWxrev($message);
                break;
            default:
                $errors[] = 'Jenis pesan tidak dikenali.';
        }

        return $errors;
    }


    /**
     * Verifikasi format METAR.
     *
     * @param string $message
     * @return array
     */
    private function verifyMetar(string $message): array
    {
        $errors = [];

        $heading = setting('bmkgsatu_heading_metar');
        $stationIcaoId = setting('station_icao_id');

        if (!preg_match('/' . preg_quote($heading) . '/', $message)) {
            $errors[] = "Pesan harus mengandung {$heading}.";
        }

        if (!preg_match('/' . preg_quote($stationIcaoId) . '/', $message)) {
            $errors[] = "Pesan harus mengandung {$stationIcaoId}.";
        }

        return $errors;
    }


    /**
     * Verifikasi format SYNOP.
     *
     * @param string $message
     * @return array
     */
    private function verifySynop(string $message): array
    {
        return [];
    }

    /**
     * Verifikasi format cuaca harian (DCH).
     *
     * @param string $message
     * @return array
     */
    private function verifyDch(string $message): array
    {

        return [];
    }

    /**
     * Verifikasi format WXREV.
     *
     * @param string $message
     * @return array
     */
    private function verifyWxrev(string $message): array
    {
        return [];
    }

    /**
     * Verifikasi format TAF.
     *
     * @param string $message
     * @return array
     */
    private function verifyTaf(string $message): array
    {
        return [];
    }

    /**
     * Verifikasi format SPECI.
     *
     * @param string $message
     * @return array
     */
    private function verifySpeci(string $message): array
    {
        return [];
    }
}
