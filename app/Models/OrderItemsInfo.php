<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemsInfo extends Model
{
    public function ProductPhotoInfo(){
        return $this->hasMany('App\Models\ProductPhotoInfos','product_id','product_id')->with('ProductPhotoData');
    }
    
    public function ProductReturnInfo(){
        return $this->belongsTo('App\Models\ProductReturnRequestInfos','id','order_item_pk');
    }
}
