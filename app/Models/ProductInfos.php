<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInfos extends Model
{
    public function ProductInfoTypeData(){
        return $this->belongsTo('App\Models\ProductInfoTypes','product_info_type_id');
    }
}
