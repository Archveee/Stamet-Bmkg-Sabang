<?php

namespace App\Filament\Widgets;

use App\Models\Peralatan;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;

class MonitoringPeralatanWidget extends Widget
{
    protected static string $view = 'filament.widgets.monitoring-peralatan-widget';

    protected static bool $isLazy = true;

    protected function getViewData(): array
    {
        $listPeralatan = Peralatan::query()
            ->where('monitoring_tidak', true)
            ->get()
            ->map(function ($item) {
                $item->off_minutes = 15; #minutes
                $item->off_max = 60; #minutes
                $item->diff_in_minutes = $item->last_update ? $item->last_update->diffInMinutes() : null;
                $item->online_offline = $item->diff_in_minutes >= $item->off_minutes ? 'offline' : 'online';
                $item->last_update_formatted = $item->last_update ? $item->last_update->diffForHumans() : null;
                return $item;
            })
            ->sortBy('sort');

        return ['listPeralatan' => $listPeralatan->toArray()];
    }
}
