<?php

namespace App\Filament\Widgets;

use App\Helpers\DateHelper;
use App\Jobs\FetchGtsMessagesJob;
use App\Models\GtsMessage;
use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class MonitoringGtsMessages extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static bool $isLazy = true;

    protected static string $view = 'filament.widgets.monitoring-gts-messages';

    protected function getViewData(): array
    {
        $messageTypes = [
            '1' => 'SINOPTIK',
            '4' => 'METAR',
            '6' => 'TAF',
            '8' => 'WXREV',
            '53' => 'DCH'
        ];

        $todayMessages = GtsMessage::query()
            ->whereDate('timestamp_data', today())
            ->select(['id', 'type_message', 'timestamp_data', 'timestamp_sent_data', 'sandi_gts'])
            ->get()
            ->map(function ($message) {
                $timestampData = \Carbon\Carbon::parse($message->timestamp_data);
                $timestampSent = $message->timestamp_sent_data
                    ? \Carbon\Carbon::parse($message->timestamp_sent_data)
                    : null;

                return [
                    'id' => $message->id,
                    'type_message' => $message->type_message,
                    'sandi_gts' => $message->sandi_gts,
                    'timestamp_data' => $timestampData->format('Y-m-d H:i:s'),
                    'timestamp_sent_data' => $timestampSent?->format('Y-m-d H:i:s'),
                    'delay_minutes' => $timestampSent ? $timestampData->diffInMinutes($timestampSent, false) : null,
                ];
            });

        $messages = $todayMessages
            ->groupBy(function ($message) use ($messageTypes) {
                $hour = \Carbon\Carbon::parse($message['timestamp_data'])->hour;

                $typeLabel = $messageTypes[$message['type_message']];
                return $typeLabel . '_' . $hour;
            });

        return ['messages' => $messages->toArray()];
    }

    public function syncAction(): Action
    {
        return Action::make('sync')
            ->label('Refresh')
            ->size('sm')
            ->outlined()
            ->icon('heroicon-o-arrow-path')
            ->action(function () {
                FetchGtsMessagesJob::dispatchSync(1);
                FetchGtsMessagesJob::dispatchSync(4);
                FetchGtsMessagesJob::dispatchSync(6);
                // Cek wxrev dan dch hanya perlu di jam 0
                if (now()->hour == 0) {
                    FetchGtsMessagesJob::dispatchSync(8);
                    FetchGtsMessagesJob::dispatchSync(53);
                }
                $this->render();
                Notification::make()
                    ->title('Data GTS berhasil disinkronkan dengan bmkgsatu')
                    ->success()
                    ->send();
            });
    }
}
