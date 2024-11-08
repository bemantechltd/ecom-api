<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGenericInfos extends Model
{
    public function GenericsDataInfo(){
        return $this->belongsTo('App\Models\Generics','product_generic_id');
    }
}
