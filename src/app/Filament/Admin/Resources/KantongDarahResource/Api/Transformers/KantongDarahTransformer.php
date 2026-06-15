<?php
namespace App\Filament\Admin\Resources\KantongDarahResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\KantongDarah;

/**
 * @property KantongDarah $resource
 */
class KantongDarahTransformer extends JsonResource
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
