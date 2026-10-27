<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Select;
use App\Models\ObserverDuty;
use Filament\Actions\Action;
use Filament\Notifications\Notification;


class InputObserverAction
{
    public static function make()
    {
        return Action::make('inputObserverOnDuty')
            ->label('Siapa Observer on Duty?')
            ->icon('heroicon-o-user')
            ->color('success')
            ->modalHeading('Nama Observer')
            ->modalDescription('Nama observer saat ini (bukan berdasar jadwal dinas)')
            ->modalWidth('xl')
            ->modalSubmitActionLabel('Simpan')
            ->modalCancelAction(false)
            ->visible(function () {
                $now = now();
                $hour = $now->hour;
                $today = $now->format('Y-m-d');

                $duty = ObserverDuty::whereDate('date', $today)->first();

                // Muncul hanya saat jam 0 atau 12 UTC
                if ($hour >= 0 && $hour < 12) {
                    // Jam 0, tampil jika observer_1 masih null
                    return !($duty && $duty->observer_1);
                } elseif ($hour >= 12 && $hour < 24) {
                    // Jam 12, tampil jika observer_2 masih null
                    return !($duty && $duty->observer_2);
                }

                return false;
            })
            ->form([
                Select::make('observer')
                    ->label('')
                    ->required()
                    ->placeholder('Pilih nama')
                    ->options(function () {
                        $observersData = setting('bmkgsatu_observer_list');
                        // Urutkan berdasarkan nama
                        usort($observersData, function ($a, $b) {
                            return strcmp($a['observer_name'], $b['observer_name']);
                        });

                        $observers = [];
                        foreach ($observersData as $observer) {
                            $observers[$observer['observer_id']] = $observer['observer_name'];
                        }

                        return $observers;
                    }),

            ])
            ->action(function (array $data) {

                $now = now();
                $hour = $now->hour;
                $today = $now->format('Y-m-d');

                $duty = ObserverDuty::firstOrNew(['date' => $today]);

                if ($hour >= 0 && $hour < 12) {
                    $duty->observer_1 = $data['observer'];
                } elseif ($hour >= 12 && $hour < 24) {
                    $duty->observer_2 = $data['observer'];
                }

                $duty->save();

                Notification::make()
                    ->title('Terima Kasih')
                    ->body('Nama observer on duty sudah disimpan.')
                    ->success()
                    ->duration(3000)
                    ->send();
            });
    }
}
