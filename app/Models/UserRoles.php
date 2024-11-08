<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model
{
    public function RoleDtlInfo(){
        return $this->belongsTo('App\Models\UserRoleInfos','role_id');
    }

    public function RoleAccesses(){
        return $this->hasMany('App\Models\UserRoleAccess','role_id','role_id');
    }
}
