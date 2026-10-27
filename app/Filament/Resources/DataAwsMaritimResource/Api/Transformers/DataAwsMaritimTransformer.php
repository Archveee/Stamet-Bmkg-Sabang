<?php
namespace App\Filament\Resources\DataAwsMaritimResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\DataAwsMaritim;

/**
 * @property DataAwsMaritim $resource
 */
class DataAwsMaritimTransformer extends JsonResource
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
