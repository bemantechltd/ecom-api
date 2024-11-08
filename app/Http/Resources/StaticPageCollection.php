<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StaticPagePhotoInfoCollection;

class StaticPageCollection extends JsonResource
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
            'page_title'            => $this->page_title,
            'details'               => $this->details,
            'photo_infos'           => StaticPagePhotoInfoCollection::collection($this->PhotoInfos),
            'display_on'            => $this->display_on,
            'slug'                  => $this->slug,
            'status'                => $this->status,
            'created_by'            => $this->created_by,
            'updated_by'            => $this->updated_by,
            'created_at'            => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'            => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}
