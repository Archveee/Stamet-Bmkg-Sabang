<x-filament-widgets::widget>
    <x-filament::section>
        <h1 class="font-semibold mb-2 text-md">{{$imageTitle}}</h1>
        <div
            x-data="{
                rawImages: @js($images ?? []),
                images: [],
                index: 0,
                isPlaying: false,
                interval: null,
                showZoom: false,
                zoomLevel: 1,
                isDragging: false,
                startX: 0,
                startY: 0,
                translateX: 0,
                translateY: 0,
                lastTranslateX: 0,
                lastTranslateY: 0,

                init() {
                    this.updateImages();

                    // 🛡️ FIXED: Menggunakan this.$wire.$on() lokal agar tidak terjadi memory leak di Livewire v3
                    this.$wire.$on('imagesUpdated', () => {
                        this.updateImages();
                    });
                },

                updateImages() {
                    if (this.$wire.images && this.$wire.images.length > 0) {
                        this.rawImages = this.$wire.images;
                        this.images = [...this.rawImages].reverse();
                        this.index = this.images.length - 1;
                        this.preloadImages();
                    }
                },

                get currentImage() {
                    if (this.images.length === 0) return '';
                    return this.images[this.index] ?? '';
                },

                get canDrag() {
                    return this.zoomLevel > 1;
                },

                next() {
                    if (this.images.length === 0) return;
                    if (this.index < this.images.length - 1) {
                        this.index++;
                    } else {
                        this.index = 0;
                    }
                    this.preloadImages();
                },

                prev() {
                    if (this.images.length === 0) return;
                    if (this.index > 0) {
                        this.index--;
                    } else {
                        this.index = this.images.length - 1;
                    }
                    this.preloadImages();
                },

                togglePlay() {
                    if (this.images.length === 0) return;
                    if (this.isPlaying) {
                        clearInterval(this.interval);
                        this.isPlaying = false;
                    } else {
                        this.index = 0;
                        this.interval = setInterval(() => {
                            this.next();
                        }, 1000);
                        this.isPlaying = true;
                    }
                },

                preloadImages() {
                    if (this.images.length <= 1) return;
                    [1, 2].forEach(offset => {
                        const idx = (this.index + offset) % this.images.length;
                        const img = new Image();
                        img.src = this.images[idx];
                    });
                },

                openZoom() {
                    this.index = this.images.length - 1;
                    this.showZoom = true;
                    this.zoomLevel = 1;
                    this.resetDragPosition();
                    if (this.isPlaying) {
                        this.togglePlay();
                    }
                },

                closeZoom() {
                    clearInterval(this.interval);
                    this.isPlaying = false;
                    this.showZoom = false;
                    this.zoomLevel = 1;
                    this.resetDragPosition();
                },

                zoomIn() {
                    if (this.zoomLevel < 3) {
                        this.zoomLevel += 0.25;
                    }
                },

                zoomOut() {
                    if (this.zoomLevel > 0.5) {
                        this.zoomLevel -= 0.25;
                        if (this.zoomLevel <= 1) {
                            this.resetDragPosition();
                        }
                    }
                },

                startDrag(e) {
                    if (!this.canDrag) return;
                    this.isDragging = true;
                    this.startX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
                    this.startY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
                },

                drag(e) {
                    if (!this.isDragging) return;
                    e.preventDefault();
                    const currentX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
                    const currentY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
                    const deltaX = currentX - this.startX;
                    const deltaY = currentY - this.startY;
                    this.translateX = this.lastTranslateX + deltaX;
                    this.translateY = this.lastTranslateY + deltaY;
                },

                endDrag() {
                    if (!this.isDragging) return;
                    this.lastTranslateX = this.translateX;
                    this.lastTranslateY = this.translateY;
                    this.isDragging = false;
                },

                resetDragPosition() {
                    this.translateX = 0;
                    this.translateY = 0;
                    this.lastTranslateX = 0;
                    this.lastTranslateY = 0;
                    this.isDragging = false;
                },

                changeImage() {
                    this.resetDragPosition();
                }
            }"
            class="w-full"
            wire:poll.60s="refreshCitra"
        >
            <div class="w-full">
                <template x-if="images && images.length > 0">
                    <div class="relative">
                        <div class="w-full aspect-video rounded relative">
                            <img :src="currentImage" class="w-full h-auto max-h-full object-contain" loading="lazy" alt="{{$imageTitle}}">
                            <button @click="openZoom" class="absolute top-0 right-0 text-gray-600 dark:text-gray-300 hover:text-primary-600 transition-colors p-1 focus:outline-none">
                                <x-heroicon-o-arrow-top-right-on-square class="w-6 h-6" />
                            </button>
                        </div>

                        <!-- Modal zoom -->
                        <div x-show="showZoom"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-90"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-90"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
                            @click.self="closeZoom">
                            <div class="relative p-1 rounded-lg max-w-full max-h-full w-full">
                                <div class="relative overflow-hidden" :class="{'cursor-grab': canDrag && !isDragging, 'cursor-grabbing': isDragging, 'cursor-default': !canDrag}">
                                    <div class="w-full h-full flex items-center justify-center"
                                        @mousedown="startDrag"
                                        @mousemove="drag"
                                        @mouseup="endDrag"
                                        @mouseleave="endDrag"
                                        @touchstart="startDrag"
                                        @touchmove="drag"
                                        @touchend="endDrag"
                                        @touchcancel="endDrag">

                                        <img :src="currentImage"
                                            class="w-full h-auto max-h-[90vh] object-contain transition-transform duration-200 ease-in-out select-none"
                                            :style="`transform: scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`"
                                            alt="{{$imageTitle}}"
                                            draggable="false">
                                    </div>

                                    <div class="absolute top-2 right-2 flex space-x-2 bg-black bg-opacity-25 rounded-lg p-1">
                                        <button @click.stop="zoomOut()" class="text-white hover:text-gray-300 transition-colors p-1 focus:outline-none">
                                            <x-heroicon-o-minus-circle class="w-5 h-5" />
                                        </button>
                                        <button @click.stop="zoomIn()" class="text-white hover:text-gray-300 transition-colors p-1 focus:outline-none">
                                            <x-heroicon-o-plus-circle class="w-5 h-5" />
                                        </button>
                                        <button @click.stop="resetDragPosition(); zoomLevel = 1;" class="text-white hover:text-gray-300 transition-colors p-1 focus:outline-none">
                                            <x-heroicon-o-arrows-pointing-in class="w-5 h-5" />
                                        </button>
                                        <button @click.stop="closeZoom()" class="text-white hover:text-red-500 transition-colors p-1 focus:outline-none">
                                            <x-heroicon-o-x-mark class="w-5 h-5" />
                                        </button>
                                    </div>

                                    <div class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white px-2 py-0.5 rounded text-sm">
                                        <span x-text="`${Math.round(zoomLevel * 100)}%`"></span>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-center">
                                    <input type="range"
                                        min="0"
                                        :max="images.length - 1"
                                        x-model="index"
                                        class="w-full max-w-[90%] h-0.5 bg-gray-500 rounded-lg appearance-none cursor-pointer"
                                        @input="preloadImages(); resetDragPosition();" />
                                </div>

                                <div class="mt-4 flex justify-center items-center space-x-6 pb-2">
                                    <button @click="prev(); resetDragPosition();" class="text-white hover:text-primary-600 transition-colors p-1 focus:outline-none">
                                        <x-heroicon-o-backward class="w-5 h-5" />
                                    </button>
                                    <button @click="togglePlay(); resetDragPosition();" class="text-white hover:text-primary-600 transition-colors p-1 focus:outline-none">
                                        <template x-if="isPlaying">
                                            <x-heroicon-o-pause class="w-5 h-5" />
                                        </template>
                                        <template x-if="!isPlaying">
                                            <x-heroicon-o-play class="w-5 h-5" />
                                        </template>
                                    </button>
                                    <button @click="next(); resetDragPosition();" class="text-white hover:text-primary-600 transition-colors p-1 focus:outline-none">
                                        <x-heroicon-o-forward class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="!images || images.length === 0">
                    <div class="w-full aspect-video flex items-center justify-center bg-gray-500 rounded">
                        <p class="text-gray-100">Belum ada image yang tersedia saat ini.</p>
                    </div>
                </template>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
