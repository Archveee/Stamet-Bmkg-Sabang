<?php

namespace App\Http\Controllers;

use App\Services\BmkgAviationApiService;
use App\Services\TafReportExcelService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TafVerificationController extends Controller
{
    /**
     * Menampilkan halaman form verifikasi (Opsional jika pakai Blade).
     */
    public function index()
    {
        return view('taf.index');
    }

    /**
     * Memproses scraping data BMKG, verifikasi, dan pemuatan laporan Excel.
     */
    public function process(
        Request $request,
        BmkgAviationApiService $apiService,
        TafReportExcelService $excelService
    ) {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
            'icao'  => 'nullable|string|max:4',
        ]);

        $icao  = strtoupper($request->input('icao', 'WITN'));
        $start = Carbon::parse($request->input('start'), 'UTC');
        $end   = Carbon::parse($request->input('end'), 'UTC');

        try {
            // 1. Ambil data TAF & METAR dari portal BMKG
            $data = $apiService->collectData($icao, $start, $end);

            // 2. Buat file laporan Excel
            $fileName = $excelService->generate($start, $data['tafs'], $data['observations']);

            // 3. Kembalikan URL untuk mengunduh file
            return response()->json([
                'success'      => true,
                'message'      => 'Verifikasi TAF berhasil diproses.',
                'download_url' => Storage::url($fileName),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses verifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}