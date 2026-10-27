<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CctvWidget;
use Filament\Pages\Page;

class Cctv extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static string $view = 'filament.pages.cctv';

    protected ?string $heading = 'CCTV';

    protected static ?string $navigationLabel = 'CCTV';

    protected static ?int $navigationSort = 5;

    protected function getHeaderWidgets(): array
    {
        return [
            CctvWidget::class,
        ];
    }

    // 🛡️ FIXED: Menggunakan tipe return terpadu Filament v3 untuk kolom layout
    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 1;
    }
}
