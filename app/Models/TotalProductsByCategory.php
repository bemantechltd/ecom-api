<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalProductsByCategory extends Model
{
    public function Category(){
        return $this->belongsTo('App\Models\Categories','category_id')->select('id','category_name');
    }
}
