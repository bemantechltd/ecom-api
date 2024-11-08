<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPhotoInfos extends Model
{
    public function ProductPhotoData(){
        return $this->belongsTo('App\Models\MediaGallery','product_photo_id');
    }
}
