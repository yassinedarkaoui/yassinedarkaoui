<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    protected $table = 'transactioncategory';
    function transaction(){
        return $this->hasOne('App\Model\Transaction','order_number','order_number')->with('user');
    }
    function soldTransaction(){
        return $this->hasOne('App\Model\Transaction','order_number','order_number')->with('user');
    }
}
