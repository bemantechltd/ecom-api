<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalProductsByProductType extends Model
{
    public function ProductType(){
        return $this->belongsTo('App\Models\ProductTypes','product_type_id')->select('id','type_title');
    }
}
