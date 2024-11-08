<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function OperatorInfo(){
        return $this->belongsTo('App\User','created_by');
    }
    
    public function UpdateOperatorInfo(){
        return $this->belongsTo('App\User','updated_by');
    }

    public function CompanyInfo(){
        return $this->hasOne('App\Models\ProductCompanyInfos','product_id')->with('CompanyDataInfo');
    }

    public function CompanyIdInfo(){
        return $this->hasOne('App\Models\ProductCompanyInfos','product_id');
    }

    public function ProductTypeInfo(){
        return $this->hasOne('App\Models\ProductTypeInfos','product_id')->with('ProductTypeDataInfo');
    }

    public function ProductTypeIdInfo(){
        return $this->hasOne('App\Models\ProductTypeInfos','product_id');
    }

    public function ProductInfos(){
        return $this->hasMany('App\Models\ProductInfos','product_id')->with('ProductInfoTypeData');
    }

    public function ProductPriceInfos(){
        return $this->hasMany('App\Models\ProductPriceInfos','product_id')->with('ProductPriceTypeData');
    }

    public function ProductPhotoInfos(){
        return $this->hasMany('App\Models\ProductPhotoInfos','product_id')->with('ProductPhotoData');
    }
    
    public function CatInfo(){
        return $this->hasMany('App\Models\ProductCatInfos','product_id')->with('CatDataInfo');
    }

    public function CatIds(){
        return $this->hasMany('App\Models\ProductCatInfos','product_id')->select('product_id','product_cat_id');
    }

    public function GenericInfo(){
        return $this->hasMany('App\Models\ProductGenericInfos','product_id')->with('GenericsDataInfo');
    }

    public function DiseaseInfo(){
        return $this->hasMany('App\Models\ProductDiseaseInfos','product_id')->with('DiseaseDataInfo');
    }

    public function TagInfo(){
        return $this->hasMany('App\Models\ProductTagInfos','product_id')->with('TagsDataInfo');
    }

    public function TagIds(){
        return $this->hasMany('App\Models\ProductTagInfos','product_id')->select('product_id','product_tag_id');
    }
}
