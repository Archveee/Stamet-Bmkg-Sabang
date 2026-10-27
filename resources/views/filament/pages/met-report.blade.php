<x-filament-panels::page>
    <form wire:submit.prevent="generateAndOpenModal" class="space-y-6">

        {{ $this->form }}

        <!-- Tombol Aksi Utama -->
        <div class="flex items-center gap-4">
            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-o-arrow-path"
                wire:click="autoFillFromDatabase">
                Load DB
            </x-filament::button>

            <x-filament::button
                type="submit"
                color="primary"
                icon="heroicon-o-magnifying-glass-plus">
                Generate & Periksa Laporan
            </x-filament::button>
        </div>
    </form>

    <!-- Pop-up Modal Preview -->
    <x-filament::modal id="preview-modal" width="3xl">
        <x-slot name="heading">
            🔍 Periksa Kembali Hasil Met Report
        </x-slot>

        <x-slot name="description">
            <span class="text-amber-500 font-semibold">
                ⚠️ Harap teliti kembali seluruh data pada gambar di bawah sebelum mengunduh file!
            </span>
        </x-slot>

        <div class="space-y-4 text-center">
            @if($previewImageUrl)
                <div class="overflow-hidden rounded-lg border border-gray-700 shadow-lg inline-block">
                    <img src="{{ $previewImageUrl }}" alt="Preview Met Report" class="max-w-full h-auto max-h-[60vh] object-contain mx-auto">
                </div>
            @else
                <p class="text-gray-400">Gambar tidak ditemukan.</p>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'preview-modal' })">
                    Tutup & Edit Lagi
                </x-filament::button>

                <x-filament::button
                    color="success"
                    icon="heroicon-o-arrow-down-tray"
                    wire:click="downloadJpeg">
                    Download JPEG
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
