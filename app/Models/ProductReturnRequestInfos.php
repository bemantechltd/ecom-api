<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReturnRequestInfos extends Model
{
    use SoftDeletes;
    
    public function OrderItemInfo(){
        return $this->belongsTo('App\Models\OrderItemsInfo','order_item_pk','id')->with('ProductPhotoInfo');
    }
    
    public function OrderInfo(){
        return $this->belongsTo('App\Models\OrderInfo','order_id','id');
    }
    
    public function ReturnReasonInfo(){
        return $this->belongsTo('App\Models\ProductReturnReason','return_reason_id','id');
    }
}
