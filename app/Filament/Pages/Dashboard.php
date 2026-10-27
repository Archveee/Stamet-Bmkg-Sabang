<?php

namespace App\Filament\Pages;

use App\Filament\Components\InputObserverAction;
use App\Filament\Widgets\MonitoringGtsMessages;
use App\Filament\Widgets\MonitoringPeralatanWidget;
use App\Filament\Widgets\MonitoringSinoptikDataWidget;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?int $navigationSort = -9;

    protected function getHeaderActions(): array
    {
        return [
            InputObserverAction::make()
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            MonitoringGtsMessages::class,
            MonitoringSinoptikDataWidget::class,
            MonitoringPeralatanWidget::class,
        ];
    }

    // 🛡️ FIXED: Fleksibilitas grid layout v3
    public function getFooterWidgetsColumns(): int | string | array
    {
        return 1;
    }
}
