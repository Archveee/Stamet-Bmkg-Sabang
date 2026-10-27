<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CitraWidget;
use Filament\Pages\Page;

class CitraCuaca extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-asia-australia';

    protected static string $view = 'filament.pages.citra-cuaca';

    protected function getHeaderWidgets(): array
    {
        return [
            CitraWidget::class,
        ];
    }

    // 🛡️ FIXED: Sinkronisasi layout grid v3
    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 1;
    }
}
