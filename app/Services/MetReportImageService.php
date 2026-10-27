<?php

namespace App\Services;

class MetReportImageService
{
    public static function generateJpeg(array $data, string $outputPath): string
    {
        $width = 1600;
        $height = 1050;

        // Create Canvas
        $img = imagecreatetruecolor($width, $height);

        // Colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $light = imagecolorallocate($img, 245, 247, 250);
        $dark  = imagecolorallocate($img, 18, 30, 55);
        $blue  = imagecolorallocate($img, 5, 61, 137);
        $line  = imagecolorallocate($img, 202, 214, 228);

        // Fill background
        imagefilledrectangle($img, 0, 0, $width, $height, $light);

        // Header Background
        imagefilledrectangle($img, 0, 0, $width, 134, $white);

        // Load Logo BMKG
        $logoFiles = [
            public_path('images/bmkg_logo.webp'),
            public_path('images/bmkg_logo.png'),
            public_path('images/bmkg_logo.jpg'),
        ];

        $logoPath = null;
        foreach ($logoFiles as $file) {
            if (file_exists($file)) {
                $logoPath = $file;
                break;
            }
        }

        if ($logoPath) {
            $logo = self::loadLogoImage($logoPath);
            if ($logo) {
                imagecopyresampled($img, $logo, 38, 22, 0, 0, 76, 92, imagesx($logo), imagesy($logo));
                imagecopyresampled($img, $logo, 110, 214, 0, 0, 92, 110, imagesx($logo), imagesy($logo));
                imagedestroy($logo);
            }
        }

        // Resolusi Font Lintas Sistem Operasi (Windows / Linux Server)
        $fontBold = self::getFontPath(true);
        $fontReg  = self::getFontPath(false);

        // Header Text
        self::drawText($img, 145, 52, "BADAN METEOROLOGI KLIMATOLOGI DAN GEOFISIKA", $dark, $fontBold, 20);
        self::drawText($img, 145, 88, "STASIUN METEOROLOGI MAIMUN SALEH SABANG", $dark, $fontBold, 22);
        self::drawText($img, 145, 120, "MET REPORT / QAM GENERATOR", $dark, $fontReg, 18);

        // Main Card Box
        imagerectangle($img, 60, 178, 1540, 930, $line);

        // Inner Card Header
        self::drawCenteredText($img, 800, 235, "BADAN METEOROLOGI KLIMATOLOGI DAN GEOFISIKA", $dark, $fontBold, 22);
        self::drawCenteredText($img, 800, 273, "STASIUN METEOROLOGI MAIMUN SALEH SABANG", $dark, $fontBold, 22);
        self::drawCenteredText($img, 800, 335, "MET REPORT / SPECIAL REPORT", $blue, $fontBold, 24);
        imagefilledrectangle($img, 95, 378, 1505, 381, $blue);

        // Format Otomatis Satuan, Tekanan (MB & INCH), serta Awan
        $wind        = self::formatUnit($data['wind'] ?? '-', 'KT');
        $visibility  = self::formatUnit($data['visibility'] ?? '-', 'KM');
        $temperature = self::formatUnit($data['temperature'] ?? '-', 'deg C');
        $dewPoint    = self::formatUnit($data['dew_point'] ?? '-', 'deg C');
        $qnh         = self::formatPressure($data['qnh'] ?? null);
        $qfe         = self::formatPressure($data['qfh'] ?? $data['qfe'] ?? null);
        $cloud       = self::formatCloud($data['cloud'] ?? '-');

        // Left Column Data
        $left = [
            ["DATE", $data['date'] ?? '-'],
            ["TIME", $data['time'] ?? '-'],
            ["AERODROME IDENTIFICATION", $data['aerodrome'] ?? 'WITN'],
            ["SURFACE WIND DIRECTION SPEED", $wind],
            ["HORIZONTAL VISIBILITY", $visibility],
            ["RUNWAY VISUAL RANGE", $data['rvr'] ?? '-'],
            ["PRESENT WEATHER", $data['weather'] ?? 'NIL'],
            ["AMOUNT AND HEIGHT OF LOW CLOUD", $cloud],
        ];

        $y = 410;
        foreach ($left as $item) {
            imagefilledrectangle($img, 110, $y + 44, 850, $y + 45, $line);
            $labelFontSize = self::calculateDynamicFontSize($item[0], $fontBold, 15, 340);
            self::drawText($img, 110, $y + 30, $item[0], $dark, $fontBold, $labelFontSize);
            self::drawText($img, 470, $y + 30, ": " . $item[1], $dark, $fontReg, 15);
            $y += 48;
        }

        // Right Column Data
        $right = [
            ["AIR TEMPERATURE", $temperature],
            ["DEW POINT TEMPERATURE", $dewPoint],
            ["QNH", $qnh],
            ["QFE", $qfe],
            ["REMARK", $data['remark'] ?? '-'],
        ];

        $y = 410;
        foreach ($right as $item) {
            imagefilledrectangle($img, 935, $y + 44, 1495, $y + 45, $line);
            $labelFontSize = self::calculateDynamicFontSize($item[0], $fontBold, 15, 270);
            self::drawText($img, 935, $y + 30, $item[0], $dark, $fontBold, $labelFontSize);
            self::drawText($img, 1220, $y + 30, ": " . $item[1], $dark, $fontReg, 15);
            $y += 54;
        }

        // Footer
        imagefilledrectangle($img, 95, 845, 1505, 848, $blue);
        self::drawText($img, 100, 890, "TIME OF ISSUE : " . ($data['issue_time'] ?? '-'), $blue, $fontBold, 16);
        self::drawText($img, 950, 890, "OBSERVER : " . ($data['observer'] ?? '-'), $blue, $fontBold, 16);

        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        imagejpeg($img, $outputPath, 95);
        imagedestroy($img);

        return $outputPath;
    }

    private static function formatUnit(?string $value, string $unit): string
    {
        if (empty($value) || $value === '-') return '-';
        $trimmed = trim($value);
        if (str_contains(strtoupper($trimmed), strtoupper($unit))) return $trimmed;
        return $trimmed . ' ' . $unit;
    }

    /**
     * Format Tekanan Udara (QNH / QFE) ke MB & INCH (Inches of Mercury)
     * Contoh Output: "1008.5 MB / 29.78 INCH" atau "1009 MB / 29.80 INCH"
     */
    private static function formatPressure(?string $value): string
    {
        if (empty($value) || $value === '-') {
            return '-';
        }

        // Jika sudah terformat lengkap sebelumnya, langsung kembalikan
        if (str_contains(strtoupper($value), 'INCH')) {
            return $value;
        }

        // Ambil hanya karakter angka dan desimal (.)
        $num = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($num) && (float) $num > 0) {
            $mbVal = (float) $num;

            // Konversi ke INCH (1 hPa/MB = 0.02953 inHg)
            $inchVal = number_format($mbVal * 0.02953, 2, '.', '');

            // Pertahankan nilai asli MB (tanpa pembulatan paksa)
            $mbFormatted = (fmod($mbVal, 1) == 0) ? (int) $mbVal : $mbVal;

            return "{$mbFormatted} MB / {$inchVal} INCH";
        }

        return $value;
    }

    /**
     * Format Parameter Awan Sesuai Standar Okta BMKG & Ketinggian 3 Digit
     */
    private static function formatCloud(?string $value): string
    {
        if (empty($value) || $value === '-') {
            return '-';
        }

        $trimmed = strtoupper(trim($value));

        // 1. Jika sudah mengandung kode awan resmi (FEW, SCT, BKN, OVC, NSC, SKC)
        if (preg_match('/^(FEW|SCT|BKN|OVC|NSC|SKC)/', $trimmed)) {
            return $trimmed;
        }

        $oktaPrefix = 'FEW';
        $heightRaw = $trimmed;

        // 2. Jika input berupa [Okta] [Ketinggian] (misal: "8 080", "8/7", "6 030")
        if (preg_match('/^([1-8])[\/\s\.-]?(\d{1,3})$/', $trimmed, $matches)) {
            $okta = (int) $matches[1];
            $heightRaw = $matches[2];

            $oktaPrefix = match (true) {
                $okta >= 1 && $okta <= 2 => 'FEW',
                $okta >= 3 && $okta <= 5 => 'SCT',
                $okta >= 6 && $okta <= 7 => 'BKN',
                $okta >= 8               => 'OVC',
                default                  => 'FEW',
            };
        }

        // 3. Otomatisasi Format Ketinggian ke 3 Digit
        if (is_numeric($heightRaw)) {
            if (strlen($heightRaw) === 1) {
                $heightFormatted = '0' . $heightRaw . '0';
            } elseif (strlen($heightRaw) === 2) {
                $heightFormatted = '0' . $heightRaw;
            } else {
                $heightFormatted = str_pad($heightRaw, 3, '0', STR_PAD_LEFT);
            }

            return $oktaPrefix . $heightFormatted;
        }

        return $trimmed;
    }

    private static function getFontPath(bool $isBold = false): string|int
    {
        $winFont = $isBold ? 'C:\\Windows\\Fonts\\arialbd.ttf' : 'C:\\Windows\\Fonts\\arial.ttf';
        if (file_exists($winFont)) {
            return $winFont;
        }

        $linuxFonts = $isBold ? [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ] : [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ];

        foreach ($linuxFonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return 5;
    }

    private static function calculateDynamicFontSize(string $text, $font, int $initialSize, int $maxWidth): int
    {
        $size = $initialSize;
        if (is_string($font) && file_exists($font)) {
            while ($size > 10) {
                $bbox = imagettfbbox($size, 0, $font, $text);
                $width = abs($bbox[2] - $bbox[0]);
                if ($width <= $maxWidth) break;
                $size--;
            }
        }
        return $size;
    }

    private static function loadLogoImage(string $path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png'  => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            default => null,
        };
    }

    private static function drawText($img, $x, $y, $text, $color, $font, $size)
    {
        if (is_string($font) && file_exists($font)) {
            imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
        } else {
            imagestring($img, 5, $x, $y - 12, $text, $color);
        }
    }

    private static function drawCenteredText($img, $cx, $y, $text, $color, $font, $size)
    {
        if (is_string($font) && file_exists($font)) {
            $bbox = imagettfbbox($size, 0, $font, $text);
            $x = $cx - (($bbox[2] - $bbox[0]) / 2);
            imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
        } else {
            $x = $cx - (strlen($text) * 9 / 2);
            imagestring($img, 5, $x, $y - 12, $text, $color);
        }
    }
}
