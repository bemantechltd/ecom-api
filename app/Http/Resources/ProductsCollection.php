<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PharmaCompaniesCollection;
use App\Http\Resources\ProductTypesCollection;
use App\Http\Resources\ProductPhotoInfoCollection;

class ProductsCollection extends JsonResource
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
            'product_title'         => $this->product_title,            
            'cat_info'              => $this->CatInfo,
            'company_info'          => $this->CompanyInfo?new PharmaCompaniesCollection($this->CompanyInfo->CompanyDataInfo):null,
            'product_infos'         => $this->ProductInfos,
            'product_type_info'     => $this->ProductTypeInfo?new ProductTypesCollection($this->ProductTypeInfo->ProductTypeDataInfo):null,
            'product_price_infos'   => $this->ProductPriceInfos,
            'product_photo_infos'   => ProductPhotoInfoCollection::collection($this->ProductPhotoInfos), 
            'generic_info'          => $this->GenericInfo,
            'disease_info'          => $this->DiseaseInfo,
            'tag_info'              => $this->TagInfo,
            'operator_info'         => $this->OperatorInfo,
            'update_operator_info'  => $this->UpdateOperatorInfo,
            'slug'                  => $this->slug,
            'registered'            => $this->registered,
            'selected'              => $this->selected,
            'status'                => $this->status,
            'created_by'            => $this->created_by,
            'updated_by'            => $this->updated_by,
            'created_at'            => $this->created_at?date('jS, F Y H:i',strtotime($this->created_at)):$this->created_at,
            'updated_at'            => $this->updated_at?date('jS, F Y H:i',strtotime($this->updated_at)):$this->updated_at
        ];
    }
}
