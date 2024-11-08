<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaGalleriesCollection;

class StaticPagePhotoInfoCollection extends JsonResource
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
            'static_page_id'        => $this->static_page_id,            
            'page_photo_data'       => new MediaGalleriesCollection($this->PagePhotoData)
        ];
    }
}