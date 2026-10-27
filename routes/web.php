<?php

use App\Events\SinoptikDataWrittenToExcel;
use App\Http\Controllers\TafVerificationController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('');
});

Route::get('/proxy-cctv', function () {
    $serverUrl = setting('excel_server_url');
    $serverUrl = preg_replace('/^http:\/\/([^\/:]+)(:\d+)?/', 'https://$1', $serverUrl);
    $targetUrl = $serverUrl . '/app/cctv';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
        echo $data;
        return strlen($data);
    });

    header('Content-Type: multipart/x-mixed-replace; boundary=frame');
    curl_exec($ch);
    curl_close($ch);
});

Route::get('/proxy-cctv-audio', function () {
    $serverUrl = setting('excel_server_url');
    $serverUrl = preg_replace('/^http:\/\/([^\/:]+)(:\d+)?/', 'https://$1', $serverUrl);
    $targetUrl = $serverUrl . '/app/cctv-audio';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
        echo $data;
        return strlen($data);
    });

    header('Content-Type: audio/wav');
    curl_exec($ch);
    curl_close($ch);
});

Route::get('/client/sinoptik/sinoptik-data-written-to-excel', function (Request $request) {
    // Mendapatkan token dari query parameter
    $token = $request->query('token');
    $dataTimestamp = $request->query('data_timestamp');
    $userId = $request->query('user_id');
    // Memverifikasi token (misalnya, dengan membandingkan dengan token yang disimpan)
    if ($token === setting('excel_server_token')) {
        $user = User::find($userId);
        if ($user) {
            // Memicu event dengan timestamp dan ID pengguna
            event(new SinoptikDataWrittenToExcel($dataTimestamp, $userId));

            return response()->json(['status' => 'success', 'message' => "Data sinoptik {$dataTimestamp} berhasil ditulis ke excel"]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    return response()->json(['status' => 'error', 'message' => 'Invalid token'], 403);
});

// Untuk keperluan backup db
Route::get('/client/backups/download-latest', function (Request $request) {
    $token = $request->query('token');
    $allowedToken = 'SABANG96001';

    // Validasi token
    if ($token !== $allowedToken) {
        return response()->json(['status' => 'error', 'message' => 'Invalid token'], 403);
    }

    $subfolder = config('backup.backup.name', 'laravel-backup');
    $backupDir = storage_path("app/backups/{$subfolder}");

    if (!File::exists($backupDir)) {
        return response()->json(['status' => 'error', 'message' => 'Folder tidak ditemukan'], 404);
    }

    $files = collect(File::files($backupDir))
        ->filter(fn($file) => str_ends_with($file->getFilename(), '.zip'))
        ->sortByDesc(fn($file) => $file->getMTime());

    if ($files->isEmpty()) {
        return response()->json(['status' => 'error', 'message' => 'Tidak ada file .zip ditemukan'], 404);
    }

    $latestFile = $files->first();

    return response()->download(
        $latestFile->getPathname(),
        $latestFile->getFilename(),
        ['Content-Type' => 'application/zip']
    );
});

// Route Baru: Verifikasi TAFOR
Route::get('/taf-verification', [TafVerificationController::class, 'index'])->name('taf.index');
Route::post('/taf-verification/process', [TafVerificationController::class, 'process'])->name('taf.process');