<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DataCuacaHarian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string $view = 'filament.pages.data-cuaca-harian';

    protected static ?int $navigationSort = -1;

    // 🛡️ FIXED: Tipe kembalian diperluas sesuai arsitektur v3
    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 1;
    }
}
