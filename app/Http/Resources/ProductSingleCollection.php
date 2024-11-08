<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductPhotoInfoCollection;

class ProductSingleCollection extends JsonResource
{
    protected function content_refresh($data){
        if(!empty($data)){
            foreach($data as $key => $val){
                $getContent = $val->ProductPhotoData->content?config('global.media_gallery_base_url').'/'.($val->ProductPhotoData->content_type==1?'images':'videos').'/'.$val->ProductPhotoData->content:null;
                $data[$key]->ProductPhotoData->content = $getContent;
            }
        }
        
        return $data;
    }

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
            'product_title'         => $this->product_title,            
            'cat_ids'               => $this->CatIds,
            'company_id_info'       => $this->CompanyIdInfo,
            'product_type_id_info'  => $this->ProductTypeIdInfo,
            'product_infos'         => $this->ProductInfos,
            'product_price_infos'   => $this->ProductPriceInfos,
            'product_photo_infos'   => ProductPhotoInfoCollection::collection($this->ProductPhotoInfos),
            'generic_info'          => $this->GenericInfo,
            'disease_info'          => $this->DiseaseInfo,
            'tag_info'              => $this->TagInfo,
            'operator_info'         => $this->OperatorInfo,
            'slug'                  => $this->slug,
            'registered'            => $this->registered,
            'selected'              => $this->selected,
            'status'                => $this->status,
            'created_by'            => $this->created_by,
            'updated_by'            => $this->updated_by,
            'created_at'            => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'            => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}