<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function SubCategories(){
        return $this->hasMany('App\Models\Categories','parent_id')->with(['SubCategories']);
    }
}
