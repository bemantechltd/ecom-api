<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaGalleriesCollection;

class ProductPhotoInfoCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'                    => $this->id,
            'product_id'            => $this->product_id,            
            'product_photo_data'    => new MediaGalleriesCollection($this->ProductPhotoData)
        ];
    }
}
