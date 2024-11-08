<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaticPageInfo extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];

    public function PhotoInfos(){
        return $this->hasMany('App\Models\StaticPagePhotoInfo','static_page_id')->with('PagePhotoData');
    }
}
