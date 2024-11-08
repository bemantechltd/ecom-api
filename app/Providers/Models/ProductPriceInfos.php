<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceInfos extends Model
{
    public function ProductPriceTypeData(){
        return $this->belongsTo('App\Models\ProductPriceTypes','product_price_type_id');
    }
}
