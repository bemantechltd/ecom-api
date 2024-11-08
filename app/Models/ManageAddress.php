<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManageAddress extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    public function RegionInfo(){
        return $this->belongsTo('App\Models\Region','region_id');
    }

    public function CityInfo(){
        return $this->belongsTo('App\Models\City','city_id');
    }

    public function AreaInfo(){
        return $this->belongsTo('App\Models\Area','area_id');
    }
}
