<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiseaseInfos extends Model
{
    public function DiseaseDataInfo(){
        return $this->belongsTo('App\Models\DiseaseInfos','product_disease_id');
    }
}
