<x-filament-widgets::widget x-data="{ isCollapsed: false }">
    <x-filament::section>
        <div class="flex justify-between items-center mb-4 w-full">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                CITRA CUACA TERKINI
            </h3>

            <div class="flex items-center">
                <div class="px-2">
                    {{ $this->refreshAction }}
                </div>

                <button
                    style="--c-300:var(--gray-300);--c-400:var(--gray-400);--c-500:var(--gray-500);--c-600:var(--gray-600);"
                    class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 -m-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 fi-color-gray mr-4"
                    type="button" wire:loading.attr="disabled"
                    x-on:click.stop="isCollapsed = !isCollapsed">

                    <!-- Chevron Down -->
                    <svg x-show="isCollapsed" x-cloak class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M14.78 7.78a.75.75 0 0 0-1.06 0L10 11.5 6.28 7.78a.75.75 0 0 0-1.06 1.06l4.25 4.25a.75.75 0 0 0 1.06 0l4.25-4.25a.75.75 0 0 0 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>

                    <!-- Chevron Up -->
                    <svg x-show="!isCollapsed" x-cloak class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M5.22 12.22a.75.75 0 0 0 1.06 0L10 8.5l3.72 3.72a.75.75 0 1 0 1.06-1.06l-4.25-4.25a.75.75 0 0 0-1.06 0L5.22 11.16a.75.75 0 0 0 0 1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="!isCollapsed" x-transition x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @livewire(App\Filament\Widgets\RadarWidget::class)
                @livewire(App\Filament\Widgets\SatelitWidget::class)
                @livewire(App\Filament\Widgets\HimawariWidget::class)
                @livewire(App\Filament\Widgets\CloudWidget::class)
                @livewire(App\Filament\Widgets\StreamlineWidget::class)
                @livewire(App\Filament\Widgets\SeaWaveWidget::class)
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>