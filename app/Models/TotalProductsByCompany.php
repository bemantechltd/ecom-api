<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalProductsByCompany extends Model
{
    public function Company(){
        return $this->belongsTo('App\Models\PharmaceuticalsCompanies','product_company_id')->select('id','company_name');
    }
}
