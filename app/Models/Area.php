<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    public function RegionInfo(){
        return $this->belongsTo('App\Models\Region','region_id');
    }

    public function CityInfo(){
        return $this->belongsTo('App\Models\City','city_id');
    }
}
