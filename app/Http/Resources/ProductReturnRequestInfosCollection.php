<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemsInfoCollection;
use App\Http\Resources\OrderInfoCollection;

class ProductReturnRequestInfosCollection extends JsonResource
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
            'order_item_info'       => new OrderItemsInfoCollection($this->OrderItemInfo),
            'order_info'            => new OrderInfoCollection($this->OrderInfo),
            'return_reason_info'    => $this->ReturnReasonInfo,
            'description'           => $this->description,
            'image_base_url'        => config('global.product_return_image_base_url'),
            'photos'                => $this->photos,
            'status'                => $this->status,
            'accept_status'         => $this->accept_status,
            'reject_reason'         => $this->reject_reason,
            'created_by'            => $this->created_by,
            'updated_by'            => $this->updated_by,
            'created_at'            => date('jS, F Y',strtotime($this->created_at)),
            'updated_at'            => date('jS, F Y',strtotime($this->updated_at))
        ];
    }
}
