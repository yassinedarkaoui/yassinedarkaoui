<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = "employee";
    function employeeUser(){
        return $this->hasOne('App\User','id','user_id');
    }
}
