<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PharmacyReport extends Model
{
    protected $table = 'pharmacy_report';
    function pharmacy(){
        return $this->hasOne('App\Model\Pharmacy','id','pharmacy');
    }
    function user(){
        return $this->hasOne('App\User','id','user');
    }
}
