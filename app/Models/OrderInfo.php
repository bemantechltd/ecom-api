<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderInfo extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function OrderItemsInfo(){
        return $this->hasMany('App\Models\OrderItemsInfo','order_id')->with(['ProductPhotoInfo','ProductReturnInfo']);
    }

    public function OrderShipBillInfo(){
        return $this->hasOne('App\Models\OrderShipBillInfo','order_id');
    }

    public function DeliveryTimelineInfo(){
        return $this->hasMany('App\Models\OrderDeliveryTimeline','order_id')->orderBy('timeline_id','DESC');
    }

    public function CustomerInfo(){
        return $this->belongsTo('App\User','customer_id')->with('UserInfo');
    }

    public function DeliveryPersonInfo(){
        return $this->hasOne('App\Models\OrderDeliveryPersonInfo','order_id')->with('User');
    }
    
    public function PrescriptionInfo(){
        return $this->hasManyThrough('App\Models\PrescriptionInfos','App\Models\OrderPrescriptionInfos','order_id','id','id','prescription_id');
    }
}
