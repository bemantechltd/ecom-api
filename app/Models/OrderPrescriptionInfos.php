<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPrescriptionInfos extends Model
{
    public function PrescriptionInfoData(){
        return $this->belongsTo('App\Models\PrescriptionInfos','prescription_id');
    }
}
