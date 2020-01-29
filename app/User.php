<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    protected $fillable = [
        'name', 'email', 'password','role','avatar','language'
    ];

    protected $hidden = [
        'password',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    function cart(){
        return $this->hasMany('App\Model\Cart','user_id');
    }
    function pharmacy(){
        return $this->hasOne('App\Model\Pharmacy','user_id');
    }
    function employee(){
        return $this->hasOne('App\Model\Employee','user_id');
    }
}
