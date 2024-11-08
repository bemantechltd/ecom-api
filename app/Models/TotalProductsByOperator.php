<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalProductsByOperator extends Model
{
    public function User(){
        return $this->belongsTo('App\User','user_id')->select('id','email')->with('UserName');
    }
}
