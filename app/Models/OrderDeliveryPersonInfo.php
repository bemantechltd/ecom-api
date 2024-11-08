<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryPersonInfo extends Model
{
    public function OrderInfo(){
        return $this->belongsTo('App\Models\OrderInfo','order_id');
    }

    public function User(){
        return $this->belongsTo('App\User','delivery_person_id')->with('UserInfo');
    }
}
