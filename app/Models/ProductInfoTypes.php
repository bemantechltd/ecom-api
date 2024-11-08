<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductInfoTypes extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function CatInfo(){
        return $this->belongsTo('App\Models\Categories','cat_id');
    }
}
