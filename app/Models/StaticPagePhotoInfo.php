<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPagePhotoInfo extends Model
{
    public function PagePhotoData(){
        return $this->belongsTo('App\Models\MediaGallery','photo_id');
    }
}
