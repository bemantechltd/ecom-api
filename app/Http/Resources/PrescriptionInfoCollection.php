<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionInfoCollection extends JsonResource
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
            'prescription_title'    => $this->prescription_title,
            'patient_name'          => $this->patient_name,
            'patient_gender_id'     => $this->patient_gender_id,
            'patient_age'           => $this->patient_age,
            'file_content'          => $this->file_name?config('global.prescription_base_url').'/'.$this->user_id.'/'.$this->file_name:null, 
            'exist_content'         => $this->file_name,           
            'file_name'             => $this->file_name,            
            'status'                => $this->status,
            'created_by'            => $this->created_by,
            'updated_by'            => $this->updated_by,
            'created_at'            => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'            => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}