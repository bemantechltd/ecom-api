<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesCollection extends JsonResource
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
            'category_name'     => $this->category_name,
            'slug'              => $this->slug,
            'parent_id'         => $this->parent_id,
            'sub_categories'    => $this->SubCategories,
            'icon'              => $this->icon?config('global.category_icon_base_url').'/'.$this->icon:null,
            'exist_icon'        => $this->icon,
            'display_on_nav'    => $this->display_on_nav,
            'display_on_body'   => $this->display_on_body,
            'status'            => $this->status,
            'created_by'        => $this->created_by,
            'updated_by'        => $this->updated_by,
            'created_at'        => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'        => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}
