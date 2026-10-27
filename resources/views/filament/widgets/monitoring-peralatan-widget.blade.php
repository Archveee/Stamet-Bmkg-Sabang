<x-filament-widgets::widget wire:poll.30s x-data="{ isCollapsed: false }">
    <x-filament::section>
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-bold text-md">MONITORING PERALATAN</h1>
            <button
                style="--c-300:var(--gray-300);--c-400:var(--gray-400);--c-500:var(--gray-500);--c-600:var(--gray-600);"
                class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 -m-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 fi-color-gray"
                type="button" wire:loading.attr="disabled"
                x-on:click.stop="isCollapsed = !isCollapsed">

                <!-- Chevron Down: Saat collapsed (konten tertutup) -->
                <svg x-show="isCollapsed" x-cloak class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M14.78 7.78a.75.75 0 0 0-1.06 0L10 11.5 6.28 7.78a.75.75 0 0 0-1.06 1.06l4.25 4.25a.75.75 0 0 0 1.06 0l4.25-4.25a.75.75 0 0 0 0-1.06Z"
                        clip-rule="evenodd" />
                </svg>

                <!-- Chevron Up: Saat expanded (konten terbuka) -->
                <svg x-show="!isCollapsed" x-cloak class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.22 12.22a.75.75 0 0 0 1.06 0L10 8.5l3.72 3.72a.75.75 0 1 0 1.06-1.06l-4.25-4.25a.75.75 0 0 0-1.06 0L5.22 11.16a.75.75 0 0 0 0 1.06Z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div wire:poll.60s x-show="!isCollapsed" x-transition x-cloak
            class="grid grid-flow-row md:grid-cols-2 2xl:grid-cols-3 3xl:grid-cols-4 4xl:grid-cols-5 5xl:grid-cols-6 gap-4">
            @foreach ($listPeralatan as $item)
                <div
                    class="flex rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800 flex-col">
                    <div class="flex h-full flex-col justify-center gap-2 p-6">
                        <img id="{{ $item['id'] }}"
                            style="object-fit: cover; width: 100%; max-height: 420px;"
                            class="rounded-md monitoring-image"
                            src="{{ !empty($item['image']) ? asset('storage/images/' . $item['image']) : asset('storage/images/blank.png') }}"
                            loading="lazy" alt="{{ $item['nama_alat'] }}">
                        <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            <div class="flex flex-wrap items-center gap-2">
                                {{ $item['nama_alat'] }}
                                <x-filament::badge size="sm"
                                    :color=" $item['online_offline'] == 'offline'
                                            ? ($item['diff_in_minutes'] < $item['off_minutes']
                                                ? 'warning'
                                                : ($item['diff_in_minutes'] < $item['off_max']
                                                    ? 'warning'
                                                    : 'danger'))
                                            : 'success' ">
                                    {{ $item['online_offline'] }}
                                </x-filament::badge>
                            </div>
                        </h5>
                        <p class="text-md text-gray-700 dark:text-gray-200">
                            <span class="font-semibold">Update terakhir:</span>
                            @if (!is_null($item['last_update']))
                                {{ \Carbon\Carbon::parse($item['last_update'])->setTimezone('Asia/Jakarta') }} WIB
                                [{{ $item['last_update_formatted'] }}]
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
