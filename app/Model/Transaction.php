<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table="transaction";

    function transactionCategory(){
        return $this->hasMany('App\Model\TransactionCategory','order_number','order_number');
    }
    function user(){
        return $this->hasOne('App\User','id','user_id');
    }
    function pharmacy(){
        return $this->hasOne('App\User','id','user_id');
    }
    function employee(){
        return $this->hasOne('App\User','id','user_id')->with('pharmacy');
    }
}
