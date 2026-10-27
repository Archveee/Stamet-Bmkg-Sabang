<div class="relative max-w-xl"
    x-data="{
         showOverlay: true,
         toggleOverlay() {
             this.showOverlay = !this.showOverlay;
         }
     }"
    wire:ignore>

    {{-- Tombol Toggle Info Cuaca --}}
    <button @click="toggleOverlay" type="button"
        class="absolute top-2 right-2 z-10 p-1"
        title="Tampilkan/Sembunyikan Info Cuaca">
        <template x-if="showOverlay">
            <x-heroicon-o-eye class="h-5 w-5 text-gray-100" />
        </template>
        <template x-if="!showOverlay">
            <x-heroicon-o-eye-slash class="h-5 w-5 text-gray-100" />
        </template>
    </button>

    {{-- Live Webcam --}}
    <img
        src="{{ url('/proxy-cctv') }}"
        class="w-full h-auto rounded-md shadow-md"
        alt="Live Webcam"
        onerror="this.style.display='none'">

    {{-- Overlay Info Cuaca --}}
    <div
        x-show="showOverlay"
        x-transition.opacity
        class="absolute top-2 left-2 pointer-events-none transition-opacity duration-300 z-10">
        <div class="pointer-events-auto">
            @livewire(\App\Filament\Widgets\LiveWeatherOverlay::class)
        </div>
    </div>

</div>