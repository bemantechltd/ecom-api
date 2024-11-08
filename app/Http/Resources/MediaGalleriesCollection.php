<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaGalleriesCollection extends JsonResource
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
            'id'                => $this->id,
            'content_title'     => $this->content_title,
            'content_type'      => $this->content_type,
            'content_size'      => $this->content_size,
            'content'           => $this->content?config('global.media_gallery_base_url').'/'.($this->content_type==1?'images':'videos').'/'.$this->content:null,
            'exist_content'     => $this->content,
            'status'            => $this->status,
            'created_by'        => $this->created_by,
            'updated_by'        => $this->updated_by,
            'created_at'        => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'        => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}