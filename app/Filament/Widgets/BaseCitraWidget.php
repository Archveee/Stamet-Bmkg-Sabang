<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;

class BaseCitraWidget extends Widget
{
    protected static string $view = 'filament.widgets.citra-item-widget';
    
    public array $images = [];
    public string $imageTitle = 'CITRA';
    
    // Properti yang bisa dioverride oleh kelas turunan
    protected string $sourceDirectory = 'images';
    protected int $imagesToTake = 18; // Default 3 jam?
    
    public function mount()
    {
        $this->loadImages();
    }
    
    public function loadImages()
    {
        $dir = public_path($this->sourceDirectory);

        if (!File::exists($dir)) {
            $this->images = [];
            return;
        }

        $this->images = collect(File::files($dir))
            ->sortByDesc(fn($file) => $file->getFilename())
            ->take($this->imagesToTake)
            ->map(fn($file) => asset($this->sourceDirectory . '/' . $file->getFilename()))
            ->values()
            ->toArray();
    }

    #[On('refreshCitra')] 
    public function refreshCitra()
    {
        $this->loadImages();
        $this->dispatch('imagesUpdated');
    }

    protected function getViewData(): array
    {
        return [
            'images' => $this->images,
            'imageTitle' => $this->imageTitle
        ];
    }
}