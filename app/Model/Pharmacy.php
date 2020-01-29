<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    protected $table="pharmacy";
    function pharmacyUser(){
//        return $this->hasMany('App\Model\PharmacyUser','pharmacy_id','id')->with('user');
        return $this->hasOne('App\User','id','user_id');
    }
}
