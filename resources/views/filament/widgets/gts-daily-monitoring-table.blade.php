<x-filament-widgets::widget x-data="{ isCollapsed: false }">
    <div class="fi-ta-ctn overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

        {{-- Widget Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-white/10 bg-white dark:bg-gray-900">
            <div>
                <h3 class="text-sm font-bold tracking-tight text-gray-950 dark:text-white" x-show="!isCollapsed">
                    MONITORING PERSENTASE GTS &mdash; {{ strtoupper($this->getMonthName()) }} {{ $this->year }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-show="!isCollapsed">
                    Ringkasan performa pengiriman data harian
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Filter Popover --}}
                <div x-data="{ open: false }" class="relative" x-show="!isCollapsed" x-cloak>
                    <button @click="open = !open"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <x-heroicon-c-funnel class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                        <span>Filter</span>
                    </button>
                    <div x-show="open" x-transition @click.outside="open = false"
                        class="absolute right-0 z-50 mt-2 w-72 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-lg">
                        <form wire:submit.prevent class="space-y-4">
                            {{ $this->form }}
                        </form>
                    </div>
                </div>

                {{-- Toggle Collapse --}}
                <button type="button"
                    x-on:click.stop="isCollapsed = !isCollapsed"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <x-heroicon-m-chevron-up class="h-5 w-5 transition-transform duration-200" x-bind:class="{ 'rotate-180': isCollapsed }" />
                </button>
            </div>
        </div>

        {{-- Table Grid --}}
        <div class="overflow-x-auto" x-show="!isCollapsed" x-transition x-cloak>
            <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-900 px-4 py-3 font-semibold text-gray-950 dark:text-white border-r border-gray-200 dark:border-white/10 min-w-[120px]">
                            Tanggal
                        </th>
                        @foreach ($daysInMonth as $day)
                            <th class="px-2 py-3 text-center font-semibold text-white-700 dark:text-gray-300 min-w-[40px]">
                                {{ $day }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach (['SINOPTIK', 'METAR', 'TAF'] as $section)
                        {{-- Row Header Kategori --}}
                        <tr class="bg-gray-50/50 dark:bg-white/[0.02]">
                            <td colspan="{{ count($daysInMonth) + 1 }}" class="sticky left-0 z-10 px-4 py-2 font-bold text-xs uppercase tracking-wider text-primary-600 dark:text-primary-400 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-white/10">
                                {{ $section }}
                            </td>
                        </tr>

                        {{-- Row Data --}}
                        @foreach (['Terkirim' => 'sent', 'Ontime' => 'ontime', 'Valid' => 'valid'] as $label => $key)
                            <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-2.5 font-medium text-gray-950 dark:text-white border-r border-gray-200 dark:border-white/10">
                                    {{ $label }}
                                </td>

                                @foreach ($daysInMonth as $day)
                                    @php
                                        $fieldKey = strtolower($section) . '_' . $key;
                                        $value = $data[$fieldKey][$day] ?? null;
                                        $hasKey = array_key_exists($day, $data[$fieldKey] ?? []);
                                        $numVal = $value !== null ? (float) $value : null;
                                    @endphp

                                    <td class="px-1 py-2 text-center text-xs">
                                        @if ($hasKey && $numVal !== null)
                                            @php
                                                $colorClass = match (true) {
                                                    $numVal >= 100 => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold',
                                                    $numVal >= 90 => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold',
                                                    default => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 font-semibold',
                                                };
                                            @endphp
                                            <span class="inline-block w-full py-0.5 rounded {{ $colorClass }}">
                                                {{ $value }}%
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600">&mdash;</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legenda --}}
        <div class="flex flex-wrap items-center gap-5 border-t border-gray-200 dark:border-white/10 px-5 py-3 text-xs text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900" x-show="!isCollapsed">
            <span class="font-semibold text-gray-950 dark:text-white">Status:</span>
            <div class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>100% (Sempurna)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span>90–99% (Perhatian)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                <span>&lt; 90% (Kurang)</span>
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
