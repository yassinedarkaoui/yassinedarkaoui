<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PharmacyUser extends Model
{
    protected $table = 'pharmacy_user';

    function user(){
        return $this->hasOne('App\User','id','user_id');
    }
}
