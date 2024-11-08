<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PharmaCompaniesCollection extends JsonResource
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
            'company_name'      => $this->company_name,
            'slug'              => $this->slug,
            'logo'              => $this->logo?config('global.company_logo_base_url').'/'.$this->logo:null,
            'exist_logo'        => $this->logo,
            'status'            => $this->status,
            'created_by'        => $this->created_by,
            'updated_by'        => $this->updated_by,
            'created_at'        => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'        => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}
