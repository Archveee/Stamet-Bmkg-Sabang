<?php

namespace App\Filament\Resources\GtsMessageResource\Pages;

use App\Filament\Components\InputObserverAction;
use App\Filament\Components\SentAndCreationModeSettingFormSchema;
use App\Jobs\SendGtsMessageJob;
use App\Filament\Resources\GtsMessageResource;
use App\Filament\Widgets\PendingGtsMessages;
use App\Helpers\DateHelper;
use App\Helpers\GtsMessageHelper;
use App\Models\DataSinoptik;
use App\Models\GtsMessage;
use App\Models\Setting;
use App\Services\DchComposer;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Services\GtsMessageVerifier;
use App\Services\MetarComposer;
use App\Services\SinoptikComposer;
use App\Services\WxrevComposer;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Actions\Action as NotificationAction;

class CreateGtsMessage extends CreateRecord
{
    protected static string $resource = GtsMessageResource::class;

    protected ?string $heading = 'Buat Pesan';

    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Submit')
            ->submit('create');
    }

    protected function getHeaderActions(): array
    {
        return [
            InputObserverAction::make(),
            Action::make('fillData')
                ->label('Autofill')
                ->slideOver()
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->modalWidth('md')
                ->modalHeading('Isi Otomatis')
                ->modalSubmitActionLabel('Isi Pesan')
                ->modalDescription('Data pembuatan pesan berasal dari data sinoptik.')
                ->form([
                    Select::make('type_message')
                        ->label('Jenis Pesan')
                        ->required()
                        ->placeholder('Pilih salah satu')
                        ->options(function () {
                            // Hanya sinoptik, metar, wxrev, dan dch
                            return array_slice(setting('bmkgsatu_type_message'), 0, 4, true);
                        })
                        ->live(debounce: 300),

                    // DateTimePicker untuk type_message selain 8 dan 53
                    DateTimePicker::make('data_timestamp')
                        ->label('Jam Pengamatan')
                        ->native(false)
                        ->suffix('UTC')
                        ->seconds(false)
                        ->displayFormat('Y-m-d H:i')
                        ->default(DateHelper::getNearestSentHour())
                        ->hidden(fn($get) => in_array($get('type_message'), ['8', '53']))
                        ->columnSpanFull(),

                    // DatePicker untuk type_message 8 dan 53
                    DatePicker::make('data_timestamp_date')
                        ->label('Tanggal Pengamatan')
                        ->native(false)
                        ->displayFormat('Y-m-d')
                        ->default(now()->startOfDay())
                        ->hidden(fn($get) => !in_array($get('type_message'), ['8', '53']))
                        ->closeOnDateSelection()
                        ->columnSpanFull(),

                ])
                ->action(function (array $data): void {
                    // Tentukan timestamp berdasarkan type_message
                    $timestamp = null;

                    if (isset($data['data_timestamp']) && !in_array($data['type_message'], ['8', '53'])) {
                        $timestamp = $data['data_timestamp'];
                    } elseif (isset($data['data_timestamp_date']) && in_array($data['type_message'], ['8', '53'])) {
                        $timestamp = Carbon::parse($data['data_timestamp_date'])->startOfDay(); // Set jam 00:00:00
                    }

                    $dataSinoptik = DataSinoptik::where('data_timestamp', $timestamp)->first();
                    $dataTimestampString = DateHelper::formatDataTimestamp($timestamp);
                    $message = null;

                    if ($dataSinoptik) {
                        if ($data['type_message'] == '4') {
                            $messageType = 'METAR';
                            $message = MetarComposer::composeMetarMessage($dataSinoptik);
                        } elseif ($data['type_message'] == '1') {
                            $messageType = 'SINOPTIK';
                            $message = SinoptikComposer::composeSinoptikMessage($dataSinoptik);
                        } elseif ($data['type_message'] == '8') {
                            $messageType = 'WXREV';
                            $message = WxrevComposer::composeWxrevMessage($timestamp);
                        } elseif ($data['type_message'] == '53') {
                            $messageType = 'DCH';
                            $message = DchComposer::composeDchMessage($timestamp);
                        }

                        // Jika ada pesan yang dibuat, isi form dan kirim notifikasi
                        if ($message) {
                            $this->form->fill(['sandi_gts' => $message]);

                            Notification::make()
                                ->title("Data {$messageType} {$dataTimestampString} berhasil dibuat")
                                ->body('Harap periksa kembali')
                                ->success()
                                ->send();
                        }
                    } else {
                        $this->form->fill(['sandi_gts' => '']);
                        Notification::make()
                            ->title("Data sinoptik {$dataTimestampString} tidak ditemukan")
                            ->warning()
                            ->send();
                    }
                }),

            Action::make('setSentAndCreationMode')
                ->label('Setting')
                ->modalHeading('Setting Mode Pesan')
                ->modalDescription('Semua offset +/- terhitung dari jam pengiriman.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->slideOver()
                ->modalWidth('md')
                ->modalSubmitActionLabel('Simpan')
                ->form(SentAndCreationModeSettingFormSchema::make())
                ->fillForm(function () {
                    $keys = [
                        'bmkgsatu_metar_sent_mode',
                        'bmkgsatu_metar_creation_mode',
                        'bmkgsatu_metar_sent_mode_offset',
                        'bmkgsatu_metar_creation_mode_offset',

                        'bmkgsatu_sinoptik_sent_mode',
                        'bmkgsatu_sinoptik_creation_mode',
                        'bmkgsatu_sinoptik_sent_mode_offset',
                        'bmkgsatu_sinoptik_creation_mode_offset',

                        'bmkgsatu_wxrev_sent_mode',
                        'bmkgsatu_wxrev_creation_mode',
                        'bmkgsatu_wxrev_sent_mode_offset',
                        'bmkgsatu_wxrev_creation_mode_offset',

                        'bmkgsatu_dch_sent_mode',
                        'bmkgsatu_dch_creation_mode',
                        'bmkgsatu_dch_sent_mode_offset',
                        'bmkgsatu_dch_creation_mode_offset',

                        'bmkgsatu_taf_sent_mode',
                        'bmkgsatu_taf_creation_mode',
                        'bmkgsatu_taf_sent_mode_offset',
                        'bmkgsatu_taf_creation_mode_offset',
                    ];

                    return collect(setting($keys))->toArray();
                })
                ->action(function (array $data): void {
                    foreach ($data as $name => $value) {
                        Setting::where('setting_name', $name)->update([
                            'setting_value' => $value,
                        ]);

                        cache()->forget('settings');
                    }

                    Notification::make()
                        ->title('Mode Pengiriman Berhasil Diupdate')
                        ->success()
                        ->duration(2000)
                        ->send();
                }),
        ];
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data Berhasil Disimpan');
    }

    protected function afterValidate(): void
    {
        // verify message
        $verifier = new GtsMessageVerifier();
        $error = $verifier->verifyMessage($this->data['sandi_gts']);
        if (!empty($error)) {
            Notification::make()
                ->title('Terdapat Kesalahan Sandi')
                ->body('Periksa kembali sebelum submit')
                ->danger()
                ->duration(10000)
                ->send();

            // abort creating process
            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['sandi_gts']) {
            $typeMessage = GtsMessageHelper::extractTypeMessage($data['sandi_gts']);
            $extractedDate = GtsMessageHelper::extractDateFromMessage($data['sandi_gts']);
            if ($typeMessage) {
                $typeMessageValue = GtsMessageHelper::getTypeMessageValue($typeMessage);
                $data['type_message'] = $typeMessageValue;
            }
            if ($extractedDate) {
                // Tetap di UTC tanpa penambahan offset
                $formattedDate = Carbon::createFromFormat('dHi', $extractedDate, 'UTC')
                    ->format('Y-m-d H:i'); // Format yang konsisten
                $data['timestamp_data'] = $formattedDate;
            }
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        // check if same message already exists
        $existingMessage = GtsMessage::where('sandi_gts', $this->data['sandi_gts'])
            ->first();

        if ($existingMessage) {
            Notification::make()
                ->title('Pesan sudah dibuat sebelumnya')
                ->body('Edit pesan?')
                ->warning()
                ->duration(10000)
                ->actions([
                    NotificationAction::make('edit')
                        ->url(GtsMessageResource::getUrl('edit', ['record' => $existingMessage]))
                        ->button(),
                ])
                ->send();

            // abort creating process
            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        $sentTimeOffset = setting(GtsMessageHelper::getSentAndCreationSettingKey($this->record->type_message, 'sent_mode_offset')) ?? 0;

        // Cek apakah pesan berjenis SPECI (Tipe 5 atau mengandung kata SPECI)
        $isSpeci = ($this->record->type_message == '5') || str_contains(strtoupper($this->record->sandi_gts ?? ''), 'SPECI');

        // Jika SPECI ATAU waktu sudah melewati offset -> Langsung kirim instan
        if ($isSpeci || now()->gte($this->record->timestamp_data->addMinutes((int) $sentTimeOffset))) {
            SendGtsMessageJob::dispatchSync($this->record->id, auth()->id());
        }
    }

    protected function getFooterWidgets(): array
    {
        return [
            PendingGtsMessages::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }
}
