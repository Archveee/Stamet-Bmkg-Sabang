<?php
namespace App\Filament\Resources\PeralatanResource\Api\Handlers;

use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\PeralatanResource;
use App\Filament\Resources\PeralatanResource\Api\Requests\UpdatePeralatanRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = PeralatanResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update Peralatan
     *
     * @param UpdatePeralatanRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdatePeralatanRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        // Jika client mengirimkan waktu sendiri
        if ($request->has('last_update')){
            $model->last_update = $request->last_update;
        } else {
            $model->last_update = now();
        }

        // Simpan file gambar
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            // Gunakan UUID untuk nama file yang tetap

            $filename = (Str::ulid() . '.' . $file->getClientOriginalExtension());

            // Simpan file gambar ke disk public/images
            $path = Storage::disk('public')->putFileAs('images', $file, $filename);

            $model->image = $filename;

            // Jika file gambar lama ada, hapus
            try {
                if (!is_null($model->getOriginal('image'))) {
                    $oldImagePath = 'images/' . $model->getOriginal('image');
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }
            } catch (\Throwable $e) {
                // Log error jika gagal menghapus gambar lama
                logger()->error('Error saat menghapus gambar lama: ' . $e->getMessage());
            }
        }

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}