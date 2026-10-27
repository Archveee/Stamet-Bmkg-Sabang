<?php
namespace App\Filament\Resources\PeralatanResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Peralatan;

/**
 * @property Peralatan $resource
 */
class PeralatanTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}
