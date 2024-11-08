<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTagInfos extends Model
{    
    public function TagsDataInfo(){
        return $this->belongsTo('App\Models\Tags','product_tag_id');
    }
}
