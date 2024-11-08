<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PromotionalBannersCollection extends JsonResource
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
            'id'                            => $this->id,
            'banner_title'                  => $this->banner_title,
            'promotional_link'              => $this->promotional_link,
            'desktop_banner_image'          => $this->desktop_banner_image?config('global.desktop_banner_image_base_url').'/'.$this->desktop_banner_image:null,
            'exist_desktop_banner_image'    => $this->desktop_banner_image,
            'mobile_banner_image'          => $this->mobile_banner_image?config('global.mobile_banner_image_base_url').'/'.$this->mobile_banner_image:null,
            'exist_mobile_banner_image'    => $this->mobile_banner_image,
            'display_type'                  => $this->display_type,
            'schedule_type'                 => $this->schedule_type,
            'start_time'                    => str_replace('+00:00', '.000Z', gmdate('c', strtotime($this->start_time))),
            'end_time'                      => str_replace('+00:00', '.000Z', gmdate('c', strtotime($this->end_time))),
            'status'                        => $this->status,
            'created_by'                    => $this->created_by,
            'updated_by'                    => $this->updated_by,
            'created_at'                    => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'                    => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}
