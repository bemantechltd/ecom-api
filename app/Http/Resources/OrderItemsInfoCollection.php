<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductPhotoInfoCollection;

class OrderItemsInfoCollection extends JsonResource
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
            'id'                        => $this->id,
            'order_id'                  => $this->order_id,
            'product_id'                => $this->product_id,
            'product_title'             => $this->product_title,
            'product_price_type_id'     => $this->product_price_type_id,
            'product_price_type'        => $this->product_price_type,
            'price'                     => $this->price,
            'qty'                       => $this->qty,
            'product_photo_info'        => ProductPhotoInfoCollection::collection($this->ProductPhotoInfo),
            'product_return_info'       => $this->ProductReturnInfo,
            'created_at'                => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'                => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}
