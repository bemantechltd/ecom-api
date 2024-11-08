<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCatInfos extends Model
{
    public function CatDataInfo(){
        return $this->belongsTo('App\Models\Categories','product_cat_id');
    }
}
