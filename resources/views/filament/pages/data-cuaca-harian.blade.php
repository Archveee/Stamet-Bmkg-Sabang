<x-filament-panels::page>
    <div x-data="{ tab: 'suhu' }" class="space-y-6">

        <!-- Custom Tabs - Centered & Width Fit Content -->
        <!-- Tabs scrollable jika overflow, tetap centered -->
        <nav class="overflow-x-auto">
            <div class="mx-auto w-max min-w-[300px]">
                <div
                    class="inline-flex gap-x-1 rounded-xl bg-white p-2 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    role="tablist">
                    <template x-for="item in [
                { key: 'suhu', label: 'Suhu' },
                { key: 'rh', label: 'Kelembapan' },
                { key: 'pressure', label: 'Tekanan' },
                { key: 'wind', label: 'Angin' },
                { key: 'txtn', label: 'Suhu Max & Min' },
                { key: 'rrr', label: 'Hujan & Penyinaran' }
            ]">
                        <button
                            type="button"
                            :key="item.key"
                            @click="tab = item.key"
                            :class="tab === item.key
                        ? 'bg-gray-50 dark:bg-white/5 text-primary-600 dark:text-primary-400'
                        : 'hover:bg-gray-50 dark:hover:bg-white/5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                            class="fi-tabs-item group flex items-center justify-center gap-x-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75"
                            role="tab"
                            x-text="item.label"></button>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Tab Panels -->
        <div x-show="tab === 'suhu'" x-cloak>
            @livewire(\App\Filament\Widgets\DchSuhuWidget::class)
        </div>
        <div x-show="tab === 'rh'" x-cloak>
            @livewire(\App\Filament\Widgets\DchRhWidget::class)
        </div>
        <div x-show="tab === 'wind'" x-cloak>
            @livewire(\App\Filament\Widgets\DchWindWidget::class)
        </div>
        <div x-show="tab === 'rrr'" x-cloak>
            @livewire(\App\Filament\Widgets\DchRainWidget::class)
        </div>
        <div x-show="tab === 'txtn'" x-cloak>
            @livewire(\App\Filament\Widgets\DchTxTnWidget::class)
        </div>
        <div x-show="tab === 'pressure'" x-cloak>
            @livewire(\App\Filament\Widgets\DchPressureWidget::class)
        </div>

    </div>
</x-filament-panels::page>