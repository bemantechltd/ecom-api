<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTypeInfos extends Model
{    
    public function ProductTypeDataInfo(){
        return $this->belongsTo('App\Models\ProductTypes','product_type_id');
    }
}
