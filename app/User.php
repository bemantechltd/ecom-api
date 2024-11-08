<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function UserName(){
        return $this->hasOne('App\Models\UserInfos','user_id')->select('user_id','full_name');
    }

    public function UserInfo(){
        return $this->hasOne('App\Models\UserInfos','user_id');
    }

    public function RoleInfo(){
        return $this->hasOne('App\Models\UserRoles','user_id')->with(['RoleDtlInfo','RoleAccesses']);
    }
    
    public function OrderDeliveryInfo(){
        return $this->hasOne('App\Models\OrderDeliveryPersonInfo','delivery_person_id')->orderBy('created_at','DESC')->with(['OrderInfo']);
    }
}
