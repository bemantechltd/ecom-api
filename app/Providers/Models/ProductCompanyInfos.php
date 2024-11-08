<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCompanyInfos extends Model
{
    public function CompanyDataInfo(){
        return $this->belongsTo('App\Models\PharmaceuticalsCompanies','product_company_id');
    }
}
