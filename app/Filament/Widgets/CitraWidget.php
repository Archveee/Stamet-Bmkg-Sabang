<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class CitraWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.citra-widget';

    public function refreshAction(): Action
    {
        return Action::make('refresh')
            ->label('Refresh')
            ->size('sm')
            ->outlined()
            ->icon('heroicon-o-arrow-path')
            ->action(function () {
                $this->dispatch('refreshCitra');
                Notification::make()
                    ->title('Gambar citra telah diperbarui')
                    ->success()
                    ->send();
            });
    }
}
