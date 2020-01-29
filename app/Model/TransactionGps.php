<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TransactionGps extends Model
{
    protected $table = "transaction_gps";
    function getUser(){
        return $this->hasOne('App\User','id','user_id');
    }
}
