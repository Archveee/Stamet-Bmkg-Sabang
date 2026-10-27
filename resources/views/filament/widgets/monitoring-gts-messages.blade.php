<x-filament-widgets::widget wire:poll.30s x-data="{ modalOpen: false, modalData: null }">
    <div style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(1, minmax(0, 1fr));" class="grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-wi gap-6 fi-page-header-widgets">
        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default] fi-wi-widget fi-wi-table">
            <div>
                <div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
                    <div class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10">
                        <div class="fi-ta-header flex flex-col gap-3 p-4 sm:px-6 sm:flex-row sm:items-center justify-between">
                            <div class="grid gap-y-1">
                                <!-- 🔍 ICON JUDUL MENGGUNAKAN HEROICON RESMI (BUKAN EMOTE) -->
                                <h3 class="fi-ta-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white flex items-center gap-2">
                                    <x-heroicon-o-queue-list class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                    <span>PANEL MONITORING DATA GTS STASIUN</span>
                                </h3>
                            </div>
                            <div>
                                {{ $this->syncAction }}
                            </div>
                        </div>
                    </div>
                    <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
                        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                        <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                JAM (UTC)
                                            </span>
                                        </span>
                                    </th>
                                    @foreach (range(0, 23) as $hour)
                                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                                <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white font-mono">
                                                    {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                                @foreach (['METAR', 'SINOPTIK', 'TAF'] as $messageType)
                                    <tr class="fi-ta-row hover:bg-gray-50/50 dark:hover:bg-white/5 [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75">
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="flex w-full justify-start text-start">
                                                    <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                        <div class="flex">
                                                            <div class="flex max-w-max">
                                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                    <span class="fi-ta-text-item-label text-sm font-bold leading-6 text-gray-950 dark:text-white">
                                                                        {{ $messageType }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach (range(0, 23) as $hour)
                                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                                <div class="fi-ta-col-wrp">
                                                    <div class="flex w-full justify-center text-center">
                                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                            @php $key = $messageType . '_' . $hour; @endphp

                                                            @if ($messageType === 'TAF' && !in_array($hour, [5, 11, 17, 23]))
                                                                <x-heroicon-c-minus class="h-5 w-5 text-gray-300 dark:text-gray-700" />
                                                            @elseif (isset($messages[$key]))
                                                                <div class="flex gap-x-1 justify-center">
                                                                    @foreach ($messages[$key] as $message)
                                                                        @php
                                                                            // INLINE CSS UNTUK MENJAMIN WARNA MUNCUL TANPA COMPILING TAILWIND
                                                                            if ($message['timestamp_sent_data']) {
                                                                                // TERKIRIM PUSAT -> HIJAU
                                                                                $inlineStyle = 'background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);';
                                                                                $iconColor = '#10b981';
                                                                            } elseif (now()->hour >= \Carbon\Carbon::parse($message['timestamp_data'])->hour) {
                                                                                // ADA DI LOKAL TAPI BELUM/TERLAMBAT KIRIM -> MERAH
                                                                                $inlineStyle = 'background-color: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3);';
                                                                                $iconColor = '#f43f5e';
                                                                            } else {
                                                                                // NETRAL / BELUM WAKTUNYA -> ABU-ABU
                                                                                $inlineStyle = 'background-color: rgba(156, 163, 175, 0.1); color: #9ca3af; border: 1px solid rgba(156, 163, 175, 0.2);';
                                                                                $iconColor = '#9ca3af';
                                                                            }
                                                                        @endphp
                                                                        <div class="py-1 px-2.5 rounded-md text-xs font-bold font-mono cursor-pointer transition flex items-center justify-center min-w-[2rem] shadow-sm"
                                                                             style="{{ $inlineStyle }}"
                                                                             @click="modalData = {{ json_encode($message) }}; modalOpen = true"
                                                                             title="Klik untuk detail sandi">
                                                                            @if ($message['timestamp_sent_data'])
                                                                                {{ (int) \Carbon\Carbon::parse($message['timestamp_data'])->diffInMinutes($message['timestamp_sent_data']) }}m
                                                                            @elseif (now()->diffInMinutes(\Carbon\Carbon::parse($message['timestamp_data']), false) < 0)
                                                                                <x-heroicon-c-minus class="h-4 w-4" style="color: {{ $iconColor }};" />
                                                                            @else
                                                                                @if (now()->hour < \Carbon\Carbon::parse($message['timestamp_data'])->hour)
                                                                                    <x-heroicon-c-minus class="h-4 w-4" style="color: {{ $iconColor }};" />
                                                                                @else
                                                                                    <x-heroicon-c-x-mark class="h-4 w-4" style="color: {{ $iconColor }};" />
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                @if ($messageType === 'SINOPTIK' && $hour % 3 !== 0)
                                                                    <x-heroicon-c-minus class="h-5 w-5 text-gray-300 dark:text-gray-700/40" />
                                                                @elseif ($hour < now()->hour)
                                                                    <!-- 🔴 DATA ABSEN / BOLONG SAMA SEKALI -> KOTAK MERAH ABSOLUT -->
                                                                    <div class="inline-flex items-center justify-center p-1 rounded-md shadow-sm"
                                                                         style="background-color: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3);">
                                                                        <x-heroicon-c-x-mark class="h-4 w-4" style="color: #f43f5e;" />
                                                                    </div>
                                                                @else
                                                                    <x-heroicon-c-minus class="h-5 w-5 text-gray-300 dark:text-gray-700/40" />
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL ELEGAN -->
    <div x-show="modalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl p-6 max-w-xl w-full relative" @click.outside="modalOpen = false">
            <h2 class="text-md font-bold text-gray-950 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-document-text class="w-5 h-5 text-gray-500" />
                <span>Detail Transmisi Sandi GTS</span>
            </h2>
            <div class="text-sm text-gray-700 dark:text-gray-300 font-sans space-y-3">
                <div class="bg-gray-950 text-emerald-400 p-4 rounded-lg font-mono text-xs border border-gray-800 shadow-inner overflow-x-auto select-all max-h-48 whitespace-pre-wrap"><span x-text="modalData?.sandi_gts"></span></div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-white/5">
                    <div>
                        <span class="text-xs text-gray-400 block">Jam Pengamatan</span>
                        <span class="font-mono font-semibold text-gray-950 dark:text-white" x-text="modalData?.timestamp_data ? modalData.timestamp_data + ' UTC' : '-'"></span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Waktu Terkirim</span>
                        <template x-if="modalData?.timestamp_sent_data">
                            <span class="font-mono font-semibold" style="color: #10b981;" x-text="modalData.timestamp_sent_data + ' UTC'"></span>
                        </template>
                        <template x-if="!modalData?.timestamp_sent_data">
                            <span style="color: #f43f5e;" class="font-medium italic">Belum Terkirim</span>
                        </template>
                    </div>
                </div>
            </div>
            <div class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-white cursor-pointer transition" @click="modalOpen = false">
                <x-heroicon-c-x-mark class="w-5 h-5" />
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
