<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GenericsCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        // return [
        //     'id'                => $this->id,
        //     'created_by'        => $this->created_by,
        //     'created_at'        => date('jS, F Y',strtotime($this->created_at)),
        //     'updated_at'        => date('jS, F Y',strtotime($this->updated_at))
        // ];
    }
}
